<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the CRM Dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'leads' => Lead::all(),
        ]);
    }
}
