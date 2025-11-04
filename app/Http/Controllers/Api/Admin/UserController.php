<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
     public function getUsers(Request $request)
    {
        try {
            $result = $this->userService->getUsers($request->search,$request->filter);
            return $this->sendResponse($result, 'Get users successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

     public function viewUser($id)
    {
        try {
            $result = $this->userService->viewUser($id);
            return $this->sendResponse($result, 'Get users successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

     public function blockUnblockToggle($id)
    {
        try {
            $result = $this->userService->blockUnblockToggle($id);
            return $this->sendResponse($result, 'Block unblock toggle successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
