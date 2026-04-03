<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class IcpListing extends Listing
{
    protected $table = 'mobileposts';

    public function details(): HasMany
    {
        return $this->hasMany(IcpListingDetail::class, 'postid', 'id');
    }
}
