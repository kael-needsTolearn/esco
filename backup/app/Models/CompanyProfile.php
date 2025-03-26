<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;
    protected $primaryKey = 'Company_Id';
    protected $casts = [
        'Company_Id' => 'string',
    ];
    protected $fillable = [
        'Company_Id',
        'Company_Name',
        'Company_Address',
        'Country',
        'Account_Manager',
        'Account_Manager_Email',
        'Contract_Start_Date',
        'Contract_End_Date',
        'Contract_Name',
    ];
}
