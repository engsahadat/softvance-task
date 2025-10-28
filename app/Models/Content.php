<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'type',
        'content',
        'file_path',
        'url',
        'order',
    ];

    /**
     * Get the module that owns the content.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
