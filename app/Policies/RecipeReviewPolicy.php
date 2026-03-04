<?php

namespace App\Policies;

use App\Models\RecipeReview;
use App\Models\User;

class RecipeReviewPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RecipeReview $recipeReview): bool
    {
        return $recipeReview->user_id !== null && $recipeReview->user_id === $user->id;
    }

    public function delete(User $user, RecipeReview $recipeReview): bool
    {
        return $recipeReview->user_id !== null && $recipeReview->user_id === $user->id;
    }
}
