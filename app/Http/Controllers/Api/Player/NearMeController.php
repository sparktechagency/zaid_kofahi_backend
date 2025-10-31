<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\NearMeService;
use Exception;
use Illuminate\Http\Request;

class NearMeController extends Controller
{
     protected $nearMeService;
    public function __construct(NearMeService $nearMeService)
    {
        $this->nearMeService = $nearMeService;
    }
     public function nearMeEvents()
    {
        try {
            $members = $this->nearMeService->nearMeEvents();
            return $this->sendResponse($members, 'Near me events successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
