<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ApiResponseController;

class CheckPasswordTokenRequest extends FormRequest
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
				'email'    => 'required|email|max:255|exists:users,email',
				'token' => 'required|max:255'
			];
    }

		protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
		{
				$response = ApiResponseController::validationErrorResponse($validator);
				throw new \Illuminate\Validation\ValidationException($validator, $response);
		}
}
