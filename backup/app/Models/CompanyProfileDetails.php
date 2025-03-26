<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfileDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'Company_Id',
        'Api_Id'
    ];
}
