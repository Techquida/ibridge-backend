<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $departments = Department::select('id', 'name')->get();
        return $this->successResponse($departments, 'Departments retrieved successfully');
    }
}
