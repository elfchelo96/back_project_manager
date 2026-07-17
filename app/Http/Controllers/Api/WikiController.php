<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WikiPage\StoreWikiPageRequest;
use App\Http\Requests\WikiPage\UpdateWikiPageRequest;
use App\Http\Resources\WikiPageResource;
use App\Models\Project;
use App\Models\WikiPage;
use App\Services\WikiPageService;
use App\Traits\ApiResponser;

class WikiController extends Controller
{
    use ApiResponser;

    public function __construct(protected WikiPageService $wikiService)
    {
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return $this->success(WikiPageResource::collection($this->wikiService->listForProject($project)));
    }

    public function show(Project $project, WikiPage $wikiPage)
    {
        $this->authorize('view', $wikiPage);

        return $this->success(new WikiPageResource($wikiPage->load(['creator', 'editor', 'project'])));
    }

    public function store(StoreWikiPageRequest $request, Project $project)
    {
        $page = $this->wikiService->create($project, $request->user(), $request->validated());

        return $this->created(new WikiPageResource($page->load(['creator', 'editor'])), 'Pagina wiki creada correctamente.');
    }

    public function update(UpdateWikiPageRequest $request, Project $project, WikiPage $wikiPage)
    {
        $page = $this->wikiService->update($wikiPage, $request->user(), $request->validated());

        return $this->success(new WikiPageResource($page), 'Pagina wiki actualizada correctamente.');
    }

    public function destroy(Project $project, WikiPage $wikiPage)
    {
        $this->authorize('delete', $wikiPage);

        $this->wikiService->delete($wikiPage);

        return $this->noContentMessage('Pagina wiki eliminada correctamente.');
    }
}
