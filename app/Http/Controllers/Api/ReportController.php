<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponser;

    public function __construct(protected ReportService $reportService)
    {
    }

    public function tasksByStatus(Request $request)
    {
        return $this->success($this->reportService->tasksByStatus($request->integer('project_id') ?: null));
    }

    public function tasksByUser(Request $request)
    {
        return $this->success($this->reportService->tasksByUser($request->integer('project_id') ?: null));
    }

    public function hoursWorked(Request $request)
    {
        return $this->success($this->reportService->hoursWorked(
            $request->input('from'),
            $request->input('to'),
            $request->integer('project_id') ?: null
        ));
    }

    public function productivity(Request $request)
    {
        return $this->success($this->reportService->productivity($request->integer('project_id') ?: null));
    }

    public function activeProjects()
    {
        return $this->success($this->reportService->activeProjects());
    }

    public function finishedProjects()
    {
        return $this->success($this->reportService->finishedProjects());
    }
}
