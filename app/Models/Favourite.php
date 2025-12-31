<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    protected $table = 'favourites';
    protected $fillable = [
        'user_id',
        'tv_id',
        'channel_id',
        'type',
    ];
}
