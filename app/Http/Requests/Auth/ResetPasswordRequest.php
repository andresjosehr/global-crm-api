<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ApiResponseController;


class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|min:8|confirmed',
            'token'    => 'required|max:255'
        ];
    }

		protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
		{
				$response = ApiResponseController::validationErrorResponse($validator);
				throw new \Illuminate\Validation\ValidationException($validator, $response);
		}
}
