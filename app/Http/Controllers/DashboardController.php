<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Models\HpsEmas;
use App\Models\HpsElektronik;
use App\Models\FaqChatbotQna;

class DashboardController extends Controller {
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Display dashboard with HPS system statistics
     */
    public function index() {
        // User statistics
        $totalUsers = \App\Models\User::count();

        // HPS Emas statistics
        $totalHpsEmas = HpsEmas::count();
        $totalHpsEmasValue = HpsEmas::sum('nilai_taksiran_rp');
        $avgHpsEmasValue = HpsEmas::avg('nilai_taksiran_rp');

        // HPS Elektronik statistics
        $totalHpsElektronik = HpsElektronik::count();
        $activeHpsElektronik = HpsElektronik::where('active', true)->count();
        $totalHpsElektronikValue = HpsElektronik::sum('harga');
        $avgHpsElektronikValue = HpsElektronik::avg('harga');

        // FAQ statistics
        $totalFaq = FaqChatbotQna::count();

        // Recent HPS Emas (last 5)
        $recentHpsEmas = HpsEmas::latest()->limit(5)->get();

        // Recent HPS Elektronik (last 5)
        $recentHpsElektronik = HpsElektronik::latest()->limit(5)->get();

        // Recent FAQ (last 5)
        $recentFaq = FaqChatbotQna::latest()->limit(5)->get();

        return view('dashboard.index', compact(
            'totalUsers',
            'totalHpsEmas',
            'totalHpsEmasValue',
            'avgHpsEmasValue',
            'totalHpsElektronik',
            'activeHpsElektronik',
            'totalHpsElektronikValue',
            'avgHpsElektronikValue',
            'totalFaq',
            'recentHpsEmas',
            'recentHpsElektronik',
            'recentFaq'
        ));
    }
}
