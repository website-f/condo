<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedItem extends Model
{
    protected $table = 'cms_deleted_items';

    protected $fillable = [
        'agent_username',
        'entity_group',
        'entity_type',
        'entity_key',
        'source_key',
        'title',
        'summary',
        'payload',
        'deleted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function scopeVisibleToAgent($query, string $username)
    {
        return $query->where(function ($builder) use ($username) {
            $builder->where('agent_username', $username)
                ->orWhereNull('agent_username');
        });
    }
}
