<?php

use App\Http\Controllers\Api\Admin\BranchController;
use App\Http\Controllers\Api\Admin\CashController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DisputeController;
use App\Http\Controllers\Api\Admin\EarningController;
use App\Http\Controllers\Api\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\Admin\LeaderBoardController as AdminLeaderBoardController;
use App\Http\Controllers\Api\Admin\PaymentController;
use App\Http\Controllers\Api\Admin\RefundController;
use App\Http\Controllers\Api\Admin\TeamController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Organizer\EventController;
use App\Http\Controllers\Api\Organizer\PerformanceController;
use App\Http\Controllers\Api\Player\DiscoverController;
use App\Http\Controllers\Api\Player\LeaderboardController;
use App\Http\Controllers\Api\Player\NearMeController;
use App\Http\Controllers\Api\ProfileContrller;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\PayPalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// social login (google)
Route::post('/social-login', [AuthController::class, 'socialLogin']);

// static page show
Route::get('pages/{slug?}', [StaticPageController::class, 'show']);

// check token valid
Route::get('/check-token', [AuthController::class, 'checkToken']);


Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/get-profile', [AuthController::class, 'getProfile']);
    Route::post('/edit-profile', [SettingsController::class, 'editProfile']);
    Route::post('/update-password', [AuthController::class, 'updatePassword']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);

    // static page update
    Route::post('pages/{slug?}', [StaticPageController::class, 'update']);

    // trnasaction
    Route::get('/get-transactions', [TransactionController::class, 'getTransactions']);
    Route::post('/withdraw', [TransactionController::class, 'withdraw']);

    // follow unfollow toggle
    Route::post('/follow-unfollow-toggle/{id?}', [FollowController::class, 'followUnfollowToggle']);


    // connected account 
    Route::post('create-connected-account', [StripeController::class, 'createConnectedAccount']);

    // payment intent and success
    Route::post('/payment-intent', [StripeController::class, 'paymentIntent']);
    Route::post('/payment-success', [StripeController::class, 'paymentSuccess']);

    // notification
    Route::get('/get-notifications', [NotificationController::class, 'getNotifications']);
    Route::patch('/read', [NotificationController::class, 'read']);
    Route::patch('/read-all', [NotificationController::class, 'readAll']);
    Route::get('/notification-status', [NotificationController::class, 'status']);


    Route::middleware('admin')->prefix('admin')->group(function () {
        // dashboard
        Route::get('/dashboard-info', [DashboardController::class, 'dashboardInfo']);

        // user
        Route::get('/get-users', [UserController::class, 'getUsers']);
        Route::get('/view-user/{id?}', [UserController::class, 'viewUser']);
        Route::patch('/block-unblock-toggle/{id?}', [UserController::class, 'blockUnblockToggle']);

        // event
        Route::get('/get-events', [AdminEventController::class, 'getEvents']);
        Route::get('/view-event/{id?}', [AdminEventController::class, 'viewEvent']);
        Route::get('/get-winners/{id?}', [AdminEventController::class, 'getWinners']);
        Route::patch('/accept-winner/{id?}', [AdminEventController::class, 'acceptWinner']);
        Route::patch('/decline-winner/{id?}', [AdminEventController::class, 'declineWinner']);
        Route::post('/prize-distribution/{id?}', [AdminEventController::class, 'prizeDistribution']);

        // team
        Route::get('/get-teams', [TeamController::class, 'getTeams']);
        Route::get('/view-team/{id?}', [TeamController::class, 'viewTeam']);

        // branch management
        Route::get('/get-branches', [BranchController::class, 'getBranches']);
        Route::post('/create-branch', [BranchController::class, 'createBranch']);
        Route::get('/view-branch/{id?}', [BranchController::class, 'viewBranch']);
        Route::patch('/edit-branch/{id?}', [BranchController::class, 'editBranch']);
        Route::delete('/delete-branch/{id?}', [BranchController::class, 'deleteBranch']);

        // cash varification
        Route::get('/get-cash-requests', [CashController::class, 'getCashRequests']);
        Route::patch('/cash-verification/{id?}', [CashController::class, 'cashVerification']);
        Route::post('/cash-single-join/{id?}', [CashController::class, 'cashSingleJoin']);
        Route::post('/cash-team-join/{id?}', [CashController::class, 'cashTeamJoin']);
        Route::delete('/delete-request/{id?}', [CashController::class, 'deleteRequest']);

        // transaction
        Route::patch('/request-accept/{id?}', [TransactionController::class, 'requestAccept']);

        // payment
        Route::get('/payment-list', [PaymentController::class, 'paymentList']);
        Route::patch('/confirm-payment/{id?}', [PaymentController::class, 'confirmPayment']);


        // earning
        Route::get('/earning-list', [EarningController::class, 'earningList']);


        // refund
        Route::get('/refund-list', [RefundController::class, 'refundList']);
        Route::patch('/confirm-refund/{id?}', [RefundController::class, 'confirmRefund']);
        Route::delete('/cancel-refund/{id?}', [RefundController::class, 'cancelRefund']);

        // leaderboard info 
        Route::get('/leader-board-info', [AdminLeaderBoardController::class, 'leaderBoardInfo']);

        // disputes
        Route::get('/get-disputes', [DisputeController::class, 'getDisputes']);
        Route::patch('/report-solve/{id?}', [DisputeController::class, 'reportSolve']);
    });

    Route::middleware('finance')->prefix('finance')->group(function () {
        // dashboard
        Route::get('/dashboard-info', [DashboardController::class, 'dashboardInfo']);

        // transaction
        Route::patch('/request-accept/{id?}', [TransactionController::class, 'requestAccept']);

        // payment
        Route::get('/payment-list', [PaymentController::class, 'paymentList']);
        Route::patch('/confirm-payment/{id?}', [PaymentController::class, 'confirmPayment']);

        // earning
        Route::get('/earning-list', [EarningController::class, 'earningList']);

        // refund
        Route::get('/refund-list', [RefundController::class, 'refundList']);
        Route::patch('/confirm-refund/{id?}', [RefundController::class, 'confirmRefund']);
        Route::delete('/cancel-refund/{id?}', [RefundController::class, 'cancelRefund']);

    });

    Route::middleware('support')->prefix('support')->group(function () {
        // dashboard
        Route::get('/dashboard-info', [DashboardController::class, 'dashboardInfo']);

        // user
        Route::get('/get-users', [UserController::class, 'getUsers']);
        Route::get('/view-user/{id?}', [UserController::class, 'viewUser']);
        Route::patch('/block-unblock-toggle/{id?}', [UserController::class, 'blockUnblockToggle']);

        // event
        Route::get('/get-events', [AdminEventController::class, 'getEvents']);
        Route::get('/view-event/{id?}', [AdminEventController::class, 'viewEvent']);
        Route::get('/get-winners/{id?}', [AdminEventController::class, 'getWinners']);
        Route::patch('/accept-winner/{id?}', [AdminEventController::class, 'acceptWinner']);
        Route::patch('/decline-winner/{id?}', [AdminEventController::class, 'declineWinner']);
        Route::post('/prize-distribution/{id?}', [AdminEventController::class, 'prizeDistribution']);

        // team
        Route::get('/get-teams', [TeamController::class, 'getTeams']);
        Route::get('/view-team/{id?}', [TeamController::class, 'viewTeam']);

        // disputes
        Route::get('/get-disputes', [DisputeController::class, 'getDisputes']);
        Route::patch('/report-solve/{id?}', [DisputeController::class, 'reportSolve']);
    });

    Route::middleware('player')->prefix('player')->group(function () {
        // discover
        Route::get('/get-events', [DiscoverController::class, 'getEvents']);
        Route::get('/view-event/{id?}', [DiscoverController::class, 'viewEvent']);
        Route::get('/get-event-details/{id?}', [DiscoverController::class, 'getEventDetails']);
        Route::post('/single-join/{id?}', [DiscoverController::class, 'singleJoin']);
        Route::post('/team-join/{id?}', [DiscoverController::class, 'teamJoin']);
        Route::post('/create-cash-request/{id?}', [DiscoverController::class, 'createCashRequest']);
        Route::get('/show-branches', [DiscoverController::class, 'showBranches']);

        // near me event
        Route::get('/near-me-events', [NearMeController::class, 'nearMeEvents']);

        // leaderboard info
        Route::get('/leaderboard-info', [LeaderboardController::class, 'leaderboardInfo']);
        Route::get('/get-sport-names-only-you-join', [LeaderboardController::class, 'getSportNamesOnlyYouJoin']);

        // profile
        Route::post('/create-team', [ProfileContrller::class, 'createTeam']);
        Route::get('/get-teams', [ProfileContrller::class, 'getTeams']);
        Route::get('/view-team/{id}', [ProfileContrller::class, 'viewTeam']);
        Route::patch('/edit-team/{id}', [ProfileContrller::class, 'editTeam']);
        Route::delete('/delete-team/{id}', [ProfileContrller::class, 'deleteTeam']);
        Route::get('/player-profile-info', [ProfileContrller::class, 'playerProfileInfo']);
        Route::post('/create-report', [ProfileContrller::class, 'createReport']);
        Route::get('/get-follower-following-list', [ProfileContrller::class, 'getFollowerFollowingList']);
        Route::patch('/share/{id?}', [ProfileContrller::class, 'share']);
    });

    Route::middleware('organizer')->prefix('organizer')->group(function () {
        // event
        Route::post('/create-event', [EventController::class, 'createEvent']);
        Route::get('/get-events', [EventController::class, 'getEvents']);
        Route::get('/view-event/{id?}', [EventController::class, 'viewEvent']);
        Route::patch('/edit-event/{id?}', [EventController::class, 'editEvent']);
        Route::delete('/delete-event/{id?}', [EventController::class, 'deleteEvent']);
        Route::get('/get-event-details/{id?}', [EventController::class, 'getEventDetails']);
        Route::post('/selected-winner/{id?}', [EventController::class, 'selectedWinner']);
        Route::delete('/remove-event-member/{id?}', [EventController::class, 'remove']);
        Route::get('/get-event-members-list/{id?}', [EventController::class, 'getEventMembersList']);
        Route::patch('/event-pay/{id?}', [EventController::class, 'eventPay']);

        // performance info
        Route::get('/performance-info', [PerformanceController::class, 'performanceInfo']);

        // profile
        Route::get('/organizer-profile-info', [ProfileContrller::class, 'organizerProfileInfo']);
    });

    Route::middleware('player.organizer')->prefix('player-organizer')->group(function () {
        //
    });

    Route::middleware('admin.finance')->prefix('admin-finance')->group(function () {
        //
    });

    Route::middleware('admin.support')->prefix('admin-support')->group(function () {
        //
    });

    Route::middleware('admin.finance.support')->prefix('admin-finance-support')->group(function () {
        //
    });

});

