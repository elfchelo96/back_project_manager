<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeEntry\StoreTimeEntryRequest;
use App\Http\Requests\TimeEntry\UpdateTimeEntryRequest;
use App\Http\Resources\TimeEntryResource;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Services\TimeEntryService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    use ApiResponser;

    public function __construct(protected TimeEntryService $timeEntryService)
    {
    }

    /**
     * Listado global de registros de tiempo (con filtros), util para reportes.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $entries = $this->timeEntryService->paginate($request->only(['task_id', 'user_id', 'from', 'to']));

        return $this->paginated($entries, TimeEntryResource::class);
    }

    public function storeForTask(StoreTimeEntryRequest $request, Task $task)
    {
        $this->authorize('manageTime', $task);

        $entry = $this->timeEntryService->create($task, $request->user(), $request->validated());

        return $this->created(new TimeEntryResource($entry), 'Registro de tiempo agregado correctamente.');
    }

    public function indexForTask(Task $task)
    {
        $this->authorize('view', $task);

        return $this->success(TimeEntryResource::collection(
            $task->timeEntries()->with('user')->latest('spent_on')->get()
        ));
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $this->authorize('manageTime', $timeEntry->task);

        $entry = $this->timeEntryService->update($timeEntry, $request->validated());

        return $this->success(new TimeEntryResource($entry), 'Registro de tiempo actualizado correctamente.');
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('manageTime', $timeEntry->task);

        $this->timeEntryService->delete($timeEntry);

        return $this->noContentMessage('Registro de tiempo eliminado correctamente.');
    }
}
