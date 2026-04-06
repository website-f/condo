<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondoListingDetail extends ListingDetail
{
    protected $connection = 'condo';
    protected $table = 'postmeta';
    protected $primaryKey = 'meta_id';
    public $timestamps = false;

    protected $fillable = ['post_id', 'meta_key', 'meta_value'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(CondoListing::class, 'post_id', 'ID');
    }

    public function getPostidAttribute(): ?int
    {
        return $this->attributes['post_id'] ?? null;
    }
}
