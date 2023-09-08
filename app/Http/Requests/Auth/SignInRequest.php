<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiResponseController;

class SignInRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'email'    => 'required|max:255',
            'password' => 'required|max:255'
        ];
    }

		protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
		{
				$response = ApiResponseController::validationErrorResponse($validator);
				throw new \Illuminate\Validation\ValidationException($validator, $response);
		}
}
