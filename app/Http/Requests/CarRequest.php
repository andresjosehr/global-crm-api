<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ApiResponseController;

class CarRequest extends FormRequest
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
		'model' => 'required',
		'license_plate' => 'string|max:6|min:6|unique:cars,license_plate,'. $this->route('car') .''

        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
	{
		$response = ApiResponseController::validationErrorResponse($validator);
		throw new \Illuminate\Validation\ValidationException($validator, $response);
	}
}
