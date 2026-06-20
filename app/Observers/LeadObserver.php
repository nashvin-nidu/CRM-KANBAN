<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\Activity;
use App\Models\User;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id() ?? $lead->assigned_to,
            'type' => 'create',
            'description' => 'New lead ' . $lead->name . ' from ' . ($lead->source ?? 'Website') . ' was created.',
        ]);
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        $userId = auth()->id();

        // 1. Check if status changed
        if ($lead->isDirty('status')) {
            $oldStatus = $lead->getOriginal('status');
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => $userId,
                'type' => 'status_change',
                'description' => 'Moved ' . $lead->name . ' to ' . $lead->status . '.',
            ]);
        }

        // 2. Check if assigned_to changed
        if ($lead->isDirty('assigned_to')) {
            $newAssigneeId = $lead->assigned_to;
            $assigneeName = 'Unassigned';
            if ($newAssigneeId) {
                $user = User::find($newAssigneeId);
                if ($user) {
                    $assigneeName = $user->name;
                }
            }

            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => $userId,
                'type' => 'assign',
                'description' => 'Assigned ' . $lead->name . ' to ' . $assigneeName . '.',
            ]);
        }

        // 3. Check if value changed
        if ($lead->isDirty('value')) {
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => $userId,
                'type' => 'value_change',
                'description' => 'Updated value of ' . $lead->name . ' to ₹' . number_format($lead->value) . '.',
            ]);
        }
    }
}
