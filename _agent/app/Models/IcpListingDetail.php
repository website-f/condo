<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcpListingDetail extends ListingDetail
{
    protected $table = 'MobilePostDetails';

    public function getConnectionName()
    {
        return IcpListing::resolvedConnectionName();
    }

    public function getTable()
    {
        return IcpListing::resolvedDetailTableName();
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(IcpListing::class, 'postid', 'id');
    }
}
