<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcpListingDetail extends ListingDetail
{
    protected $table = 'mobilepostdetails';

    public function listing(): BelongsTo
    {
        return $this->belongsTo(IcpListing::class, 'postid', 'id');
    }
}
