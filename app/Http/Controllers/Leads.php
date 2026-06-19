<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Lead;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Http\JsonResponse;

class Leads extends Controller
{
    public function index(): Response
    {
        return Inertia::render('leads/Leads', [
            'leads' => Lead::orderBy('id', 'desc')->get(),
        ]);
    }

    public function batchStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leads' => 'required|array',
            'leads.*.name' => 'required|string',
            'leads.*.email' => 'nullable|email',
            'leads.*.phone' => 'required|string',
            'leads.*.company' => 'nullable|string',
            'leads.*.status' => 'required|string',
            'leads.*.value' => 'required|numeric',
            'leads.*.source' => 'nullable|string',
            'leads.*.date' => 'required|date_format:Y-m-d',
            'leads.*.rating' => 'nullable|string',
        ]);

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($validated['leads'] as $leadData) {
            $exists = false;
            if (!empty($leadData['email'])) {
                $exists = Lead::where('email', $leadData['email'])->exists();
            }

            if (!$exists) {
                Lead::create($leadData);
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'created' => $createdCount,
            'skipped' => $skippedCount,
        ]);
    }
}
