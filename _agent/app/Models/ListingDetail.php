<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingDetail extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'PostDetails';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'postid', 'meta_key', 'meta_value'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'postid', 'id');
    }

    public function getMataValueAttribute(): ?string
    {
        return $this->attributes['meta_value'] ?? null;
    }
}
