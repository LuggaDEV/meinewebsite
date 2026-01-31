<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    /**
     * Determine if the user can create recipes.
     */
    public function create(User $user): bool
    {
        return true; // Alle authentifizierten User können Rezepte erstellen
    }

    /**
     * Determine if the user can update the recipe.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return true; // Alle authentifizierten User können Rezepte bearbeiten
    }

    /**
     * Determine if the user can delete the recipe.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return true; // Alle authentifizierten User können Rezepte löschen
    }
}
