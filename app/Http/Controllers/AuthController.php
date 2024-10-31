<?php

namespace App\Http\Controllers;

use App\Events\VerifyEmailByCode;
use App\Models\User;
use App\Http\Requests\Register;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function activeCode(Request $request)
    {
        $request->validate([
            'code_type' => 'required|in:email,mobile',
            'code' => 'required|integer'
        ]);
        if ($request->code_type == 'mobile') {
        } elseif ($request->code_type == 'email') {
            if ($request->code == AuthApi()->user()->email_code) {
                $user = AuthApi()->user();
                $user->email_code = null;
                $user->email_verified_at = now();
                $user->save();
                $message = __('main.email_active_success');
            } else {
                $message = __('main.wrong_mesg');
            }
            return res_data([], $message);
        }
    }
    public function resendActiveCode(Request $request)
    {
        $data = $request->validate([
            'code_type' => 'required|in:email,mobile'
        ]);
        if ($request->code_type == 'mobile') {
        } elseif ($request->code_type == 'email') {
            event(new VerifyEmailByCode(User::find(AuthApi()->id())));
            $message = __('main.code_send_to_email');
        }
        return res_data([], $message);
    }

    public function register(Register $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($request->password);
        $data['mobile'] = ltrim($request->mobile, '0');
        $data['email_code'] = rand(00000, 99999);
        $data['mobile_code'] = rand(00000, 99999);
        User::create($data);

        $credentials = request(['email', 'password']);

        return $this->login($credentials);
    }

    public function login(array $creden = null)
    {
        $credentials = [
            'password' => request('password')
        ];

        if (filter_var(request('account'), FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = request('account');
        } elseif (intval(request('account'))) {
            $credentials['mobile'] = ltrim(request('account'), '0');
        }

        $attempt = !empty($creden) ? $creden : $credentials;
        if (! $token = AuthApi()->attempt($attempt)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $data = [];
        $data['token'] = $this->respondWithToken($token)?->original;
        $data['need_email_verified'] = AuthApi()->user()->email_verified_at == null;
        $data['need_mobile_verified'] = AuthApi()->user()->mobile_verified_at == null;
        return res_data($data, __('main.login'),);
    }

    public function me()
    {
        $user = AuthApi()->user()->only('name', 'email', 'mobile');
        return res_data($user);
    }


    public function logout()
    {
        AuthApi()->logout();

        return res_data([], __('main.logout'));
    }

    public function refresh()
    {
        return $this->respondWithToken(AuthApi()->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => AuthApi()->factory()->getTTL() * 9999
        ]);
    }
}
