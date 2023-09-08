<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiResponseController extends Controller
{
    static public function response(String $message = '', Int $statusCode = 200, $data = false)
		{
				$response = [
						'message' => $message
				];

				if($data){
					$response['data'] = $data;
				}

				return response()->json($response, $statusCode);
		}

		static public function validationErrorResponse(\Illuminate\Contracts\Validation\Validator $validator)
		{
				return new JsonResponse([
						'message' => 'Hay errores en el formulario',
						'errors' => $validator->errors()
				], 422);
		}
}
