<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceRoom extends Model
{
    use HasFactory;
    protected $fillable = [
        'DeviceRoomID',
        'DeviceRoomName',
        'DeviceRoomLocation',
    ];
}
