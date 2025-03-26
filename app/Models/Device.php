<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $primaryKey = 'Device_Id';
    protected  $fillable = [
        "Device_Id",
        "Device_Name",
        "Device_Desc",
        "Device_Loc",
        "Room_Type",
        "Manufacturer",
        "Serial_Number",
        "Mac_Address",
        "Status",
        "Company_Id",
    ];
}
