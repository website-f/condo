<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'State';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'state', 'category'];
}
