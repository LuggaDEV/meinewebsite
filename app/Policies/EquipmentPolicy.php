<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    /**
     * Determine if the user can create equipment.
     */
    public function create(User $user): bool
    {
        return true; // Alle authentifizierten User können Equipment erstellen
    }

    /**
     * Determine if the user can update the equipment.
     */
    public function update(User $user, Equipment $equipment): bool
    {
        return true; // Alle authentifizierten User können Equipment bearbeiten
    }

    /**
     * Determine if the user can delete the equipment.
     */
    public function delete(User $user, Equipment $equipment): bool
    {
        return true; // Alle authentifizierten User können Equipment löschen
    }
}
