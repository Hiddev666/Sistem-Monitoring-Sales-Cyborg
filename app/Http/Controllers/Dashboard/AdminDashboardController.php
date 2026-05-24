<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(): View
    {
        return view('admin.dashboard');
    }
}
