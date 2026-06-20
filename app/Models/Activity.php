<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['lead_id', 'user_id', 'type', 'description'])]
class Activity extends Model
{
    protected $with = ['user', 'lead'];

    /**
     * Get the lead associated with this activity.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who performed this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
