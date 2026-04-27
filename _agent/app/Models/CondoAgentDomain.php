<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondoAgentDomain extends Model
{
    protected $table = 'condo_agent_domains';

    protected $fillable = [
        'agent_username',
        'host',
        'is_primary',
        'is_active',
        'ssl_status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_username', 'username');
    }
}
