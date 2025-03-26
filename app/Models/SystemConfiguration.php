<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory;
    protected $primaryKey = 'Code_ID';
    protected $fillable = [
        'Code_Name',
        'Code_Value',
        'Code_Description'
    ];
}
