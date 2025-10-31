<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BranchRequest;
use App\Http\Requests\Admin\CreateBranchRequest;
use App\Http\Requests\Admin\EditBranchRequest;
use App\Services\Admin\BranchService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function getBranches(): JsonResponse
    {
        try {
            $branches = $this->branchService->getBranches();
            return $this->sendResponse($branches, 'Get all branches successfully retrieved.');
        } catch (Exception $e) {
             return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function createBranch(CreateBranchRequest $request): JsonResponse
    {
        try {
            $branch = $this->branchService->createBranch($request->validated());
            return response()->json(['status' => true, 'message' => 'Branch created successfully', 'data' => $branch]);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function viewBranch($id): JsonResponse
    {
        try {
            $branch = $this->branchService->viewBranch($id);
            return response()->json(['status' => true, 'data' => $branch]);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function editBranch(EditBranchRequest $request, $id): JsonResponse
    {
        try {
            $branch = $this->branchService->editBranch($id, $request->validated());
            return response()->json(['status' => true, 'message' => 'Branch updated successfully', 'data' => $branch]);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function deleteBranch($id): JsonResponse
    {
        try {
            $this->branchService->deleteBranch($id);
            return response()->json(['status' => true, 'message' => 'Branch deleted successfully']);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
