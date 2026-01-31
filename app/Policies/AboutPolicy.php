<?php

namespace App\Policies;

use App\Models\About;
use App\Models\User;

class AboutPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, About $about): bool
    {
        return true;
    }
}
