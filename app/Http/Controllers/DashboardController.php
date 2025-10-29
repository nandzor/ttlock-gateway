<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class DashboardController extends Controller {
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Display dashboard with system statistics
     */
    public function index() {
        // User statistics
        $totalUsers = \App\Models\User::count();

        return view('dashboard.index', compact(
            'totalUsers'
        ));
    }
}
