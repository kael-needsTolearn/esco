<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCompanyProfile extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Company_Name'=>'required|string|max:100',
            'Company_Address'=>'required|string|max:100',
            'Country'=>'required|string|max:100',
            'Contract_Name'=>'required|string|max:100',
            'Contract_Start_Date'=>'required|date',
            'Contract_End_Date'=>'required|date',
            'Account_Manager'=>'required|string|max:100',
            'Account_Manager_Email'=>'required|email',
        ];
    }
}
