<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponser;

class DashboardController extends Controller
{
    use ApiResponser;

    public function __construct(protected DashboardService $dashboardService)
    {
    }

    public function index()
    {
        return $this->success($this->dashboardService->summary());
    }
}
