<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['name', 'email', 'phone', 'company', 'status', 'value', 'source', 'date', 'rating', 'assigned_to'])]
class Lead extends Model
{
    use HasFactory;

    protected $with = ['assignee'];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'date' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the user assigned to this lead.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

