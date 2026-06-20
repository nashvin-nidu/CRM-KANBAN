<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class KanBanBoard extends Controller
{
    /**
     * Display the Kanban board view.
     */
    public function index(): Response
    {
        return Inertia::render('kanban/Kanban');
    }

    /**
     * Get all leads from database.
     */
    public function getLeads(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            return response()->json(Lead::all());
        }
        return response()->json(Lead::where('assigned_to', $user->id)->get());
    }

    /**
     * Create or update a lead.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company' => 'nullable|string',
            'status' => 'required|string',
            'value' => 'required|numeric',
            'source' => 'nullable|string',
            'date' => 'required|date_format:Y-m-d',
            'rating' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $lead = null;

        if (!empty($validated['id'])) {
            $lead = Lead::find($validated['id']);
            if ($lead) {
                $lead->update($validated);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found.',
                ], 404);
            }
        } else {
            $exists = false;
            if (!empty($validated['email'])) {
                $exists = Lead::where('email', $validated['email'])->exists();
            }

            if (!$exists) {
                if ($request->user()->role !== 'admin') {
                    $validated['assigned_to'] = $request->user()->id;
                }
                $lead = Lead::create($validated);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'A lead with this email already exists.',
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'lead' => $lead,
        ]);
    }

    /**
     * Delete a lead.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $lead->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
