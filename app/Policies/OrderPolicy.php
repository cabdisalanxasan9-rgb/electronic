<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function create(User $user): bool
    {
        return $user->canManageOrders();
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || (int) $order->user_id === (int) $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->canManageOrders();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->canManageOrders();
    }
}
