<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiAccount extends Model
{
    use HasFactory;
    protected $primaryKey = 'Api_Id';
    protected $fillable = [
        'Platform',
        'Description',
        'Variable1',
        'Variable2',
        'Variable3',
        'Variable4',
        'Variable5'
    ];
}
