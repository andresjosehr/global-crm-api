<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ApiResponseController;

class ShoeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'brand' => 'required',
		'size' => 'required',
		
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
	{
		$response = ApiResponseController::validationErrorResponse($validator);
		throw new \Illuminate\Validation\ValidationException($validator, $response);
	}
}
