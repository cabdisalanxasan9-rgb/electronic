<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactAdminController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(20);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }
        return view('admin.contacts.show', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string',
        ]);

        $contact->update($validated);
        
        // Normally you'd send an email here using Laravel Mail
        // Mail::to($contact->email)->send(new ContactReplyMail($contact));

        return redirect()->route('admin.contacts.show', $contact)->with('status', 'Reply saved. (Email sending mock)');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('status', 'Contact message deleted.');
    }
}
