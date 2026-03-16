<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class InventoryPolicy
 * Enforces Role-Based Access Control (RBAC) rules for interacting with the inventory.
 */
class InventoryPolicy
{
    /**
     * Determine whether the user can receive new stock.
     * Pharmacists, Lab Techs, and Admins can receive stock.
     */
    public function receive(User $user): bool
    {
        return in_array($user->role, ['admin', 'pharmacist', 'lab_tech'], true);
    }

    /**
     * Determine whether the user can dispense stock.
     * Pharmacists, Nurses (for ward stock), and Admins can dispense stock.
     */
    public function dispense(User $user): bool
    {
        return in_array($user->role, ['admin', 'pharmacist', 'nurse'], true);
    }

    /**
     * Determine whether the user can transfer stock between locations.
     * Pharmacists, Lab Techs, and Admins can initiate transfers.
     */
    public function transfer(User $user): bool
    {
        return in_array($user->role, ['admin', 'pharmacist', 'lab_tech'], true);
    }

    /**
     * Determine whether the user can manage stock settings/locations (Administrative actions).
     */
    public function manage(User $user): bool
    {
        return in_array($user->role, ['admin', 'pharmacist'], true); // e.g., Head pharmacist
    }
}
