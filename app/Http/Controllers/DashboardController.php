<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Activity;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the CRM Dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $activities = Activity::when($user->role !== 'admin', function ($query) use ($user) {
                $query->whereHas('lead', function ($q) use ($user) {
                    $q->where('assigned_to', $user->id);
                });
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'leads' => Lead::with('assignee')->get(),
            'salesTeam' => User::where('role', 'sales_rep')->orderBy('id', 'asc')->get(),
            'activities' => $activities,
        ]);
    }
}
