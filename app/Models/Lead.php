<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['name', 'email', 'phone', 'company', 'status', 'value', 'source', 'date', 'rating'])]
class Lead extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'date' => 'date:Y-m-d',
        ];
    }
}

