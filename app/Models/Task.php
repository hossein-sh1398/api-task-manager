<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'completed'
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function (Builder $query) use ($search) {
            $query->where('title', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%");
        });
    }
    public function scopeFilter(Builder $query, $status)
    {
        return  $query->where('completed', $status === 'completed');
    }
}
