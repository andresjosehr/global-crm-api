<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\CheckPasswordTokenRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Controllers\ApiResponseController;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Auth\SignInRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    public function signIn(SignInRequest $request)
    {
        $user = User::where('email', $request->email)->where('active', true)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponseController::response('Usuario o contraseña invalida', 422);
        }

        // Configurar el TTL para una semana (60 minutos * 24 horas * 7 días)
        $myTTL = 60 * 24 * 7;
        JWTAuth::factory()->setTTL($myTTL);

        // Generar el token con el nuevo TTL
        $token = JWTAuth::fromUser($user);

        $user->modules = $user->getModules();
        $data = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'user' => $user
        ];

        return ApiResponseController::response('Autenticacion exitosa', 200, $data);
    }

    public function signInEnrollment(Request $request, $order_key)
    {
        $user = Student::where('email', $request->email)->whereHas('orders', function ($query) use ($order_key) {
            $query->where('key', $order_key);
        })->first();

        if (!$user) {
            return ApiResponseController::response('Usuario o contraseña invalida', 422);
        }

        $passwrord = $user->created_at->format('YmdHis');
        $passwrord = ($passwrord + $user->id) * 2;
        $passwrord = $passwrord * $passwrord * $user->id;
        $passwrord = substr($passwrord, -22);
        $passwrord = str_replace('E+', '', $passwrord);
        $passwrord = str_replace('.', '', $passwrord);

        $user = Student::where('email', $request->email)->first();
        if (!$user) { // $passwrord != $request->password
            return ApiResponseController::response('Usuario o contraseña invalida', 422);
        }

        // Configurar el TTL para una semana (60 minutos * 24 horas * 7 días)
        // $myTTL = 60 * 24 * 7;
        // JWTAuth::factory()->setTTL($myTTL);

        // // Generar el token con el nuevo TTL
        // $token = JWTAuth::fromUser($user);

        // $user->modules = $user->getModules();
        // $data = [
        //     'accessToken' => $token,
        //     'tokenType' => 'bearer',
        //     'user' => $user
        // ];

        return ApiResponseController::response('Autenticacion exitosa', 200, []);
    }


    public function checkAuth(Request $request)
    {

        // Get token information
        $token = JWTAuth::getToken();
        $info = JWTAuth::decode($token);

        $user = auth()->user();

        $user = User::find($user->id);

        $user->modules = $user->getModules();
        // $user->getModules();
        $data = [
            'user' => $user
        ];
        return ApiResponseController::response('Refrescado exitosamente', 200, $data);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponseController::response('Usuario no existe', 422);
        }
        // Send email
        $token = Password::broker()->createToken($user);

        $user->notify(new \App\Notifications\MailResetPasswordNotification($token, $user->email));

        return ApiResponseController::response('Correo enviado', 200, $token);
    }

    public function checkPasswordResetToken(CheckPasswordTokenRequest $request)
    {

        $reset = DB::table('password_resets')->where(['email' => $request->email])->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return ApiResponseController::response('Token invalido', 422);
        }

        // Check if the token has expired
        $expirationDate = Carbon::parse($reset->created_at)->addMinutes(config('auth.passwords.users.expire'));
        if ($expirationDate->isPast()) {
            return ApiResponseController::response('Token expirado', 422);
        }

        return ApiResponseController::response('Token valido', 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {

        $reset = DB::table('password_resets')->where(['email' => $request->email])->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return ApiResponseController::response('Token invalido', 422);
        }

        $expirationDate = Carbon::parse($reset->created_at)->addMinutes(config('auth.passwords.users.expire'));
        if ($expirationDate->isPast()) {
            return ApiResponseController::response('Token expirado', 422);
        }


        $data = $request->only('email', 'token', 'password', 'password_confirmation');

        $response = Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($response != Password::PASSWORD_RESET) {
            return ApiResponseController::response('Error al reestablecer contraseña', 422);
        }

        return ApiResponseController::response('Contraseña reestablecida, ahora puedes iniciar sesion', 200);
    }

    public function makeSession(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponseController::response('Usuario no existe', 422);
        }

        // Configurar el TTL para una semana (60 minutos * 24 horas * 7 días)
        $myTTL = 60 * 24 * 7;
        JWTAuth::factory()->setTTL($myTTL);

        // Generar el token con el nuevo TTL
        $token = JWTAuth::fromUser($user);

        $user->modules = $user->getModules();
        $data = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'user' => $user
        ];

        return ApiResponseController::response('Autenticacion exitosa', 200, $data);
    }
}
