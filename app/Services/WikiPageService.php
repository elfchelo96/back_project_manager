<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\WikiPage;
use App\Repositories\Contracts\WikiPageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WikiPageService
{
    public function __construct(protected WikiPageRepositoryInterface $wikiPages)
    {
    }

    public function listForProject(Project $project): Collection
    {
        return $project->wikiPages()->with(['creator', 'editor'])->orderBy('title')->get();
    }

    public function create(Project $project, User $user, array $data): WikiPage
    {
        return $project->wikiPages()->create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(WikiPage $page, User $user, array $data): WikiPage
    {
        $page->update(array_filter([
            'title' => $data['title'] ?? null,
            'content' => array_key_exists('content', $data) ? $data['content'] : null,
            'updated_by' => $user->id,
        ], fn ($v) => $v !== null));

        return $page->refresh()->load(['creator', 'editor']);
    }

    public function delete(WikiPage $page): void
    {
        $page->delete();
    }
}
