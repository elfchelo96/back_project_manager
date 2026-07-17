<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiPage;

class WikiPagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WikiPage $page): bool
    {
        return $page->project->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->can('wiki.manage');
    }

    public function update(User $user, WikiPage $page): bool
    {
        return $user->can('wiki.manage') && $page->project->isMember($user);
    }

    public function delete(User $user, WikiPage $page): bool
    {
        return $user->can('wiki.manage') && $page->project->isMember($user);
    }
}
