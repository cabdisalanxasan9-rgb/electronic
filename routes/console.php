<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:backup-db', function () {
    if (config('database.default') !== 'sqlite') {
        $this->error('This command currently supports sqlite only.');
        return 1;
    }

    $sqlitePath = (string) config('database.connections.sqlite.database');
    $sourcePath = str_starts_with($sqlitePath, DIRECTORY_SEPARATOR)
        ? $sqlitePath
        : base_path($sqlitePath);

    if (! File::exists($sourcePath)) {
        $this->error('SQLite database file not found at '.$sourcePath);
        return 1;
    }

    $backupPath = trim((string) env('BACKUP_PATH', 'backups/database'), '/');
    $diskName = (string) env('BACKUP_DISK', config('filesystems.default', 'local'));
    $keepDays = max(1, (int) env('BACKUP_KEEP_DAYS', 14));
    $timestamp = now()->format('Ymd_His');
    $backupFile = $backupPath.'/sqlite_'.$timestamp.'.sqlite';

    Storage::disk($diskName)->put($backupFile, File::get($sourcePath));
    $this->info('Backup created: '.$backupFile);

    $cutoff = Carbon::now()->subDays($keepDays)->timestamp;
    foreach (Storage::disk($diskName)->files($backupPath) as $storedFile) {
        $updatedAt = Storage::disk($diskName)->lastModified($storedFile);
        if ($updatedAt < $cutoff) {
            Storage::disk($diskName)->delete($storedFile);
            $this->line('Pruned old backup: '.$storedFile);
        }
    }

    return 0;
})->purpose('Create and prune sqlite backups for production safety');

Artisan::command('app:restore-db {file : Backup file path under storage disk}', function (string $file) {
    if (config('database.default') !== 'sqlite') {
        $this->error('This command currently supports sqlite only.');
        return 1;
    }

    $diskName = (string) env('BACKUP_DISK', config('filesystems.default', 'local'));
    if (! Storage::disk($diskName)->exists($file)) {
        $this->error('Backup file not found on disk '.$diskName.': '.$file);
        return 1;
    }

    $sqlitePath = (string) config('database.connections.sqlite.database');
    $targetPath = str_starts_with($sqlitePath, DIRECTORY_SEPARATOR)
        ? $sqlitePath
        : base_path($sqlitePath);

    File::ensureDirectoryExists(dirname($targetPath));
    File::put($targetPath, Storage::disk($diskName)->get($file));

    $this->info('Database restored from '.$file);
    return 0;
})->purpose('Restore sqlite database from a backup file');

Schedule::command('app:backup-db')->dailyAt('02:00');
