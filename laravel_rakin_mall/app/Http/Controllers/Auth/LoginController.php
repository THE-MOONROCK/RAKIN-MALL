<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\VerifyEmailException;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HelperClassTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use AuthenticatesUsers, HelperClassTrait;
    protected $module = 'user';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Used to authenticate user login
     * @post ("/api/login")
     * @param ({
     *      @Parameter("email", type="email", required="true", description="Email of User"),
     *      @Parameter("password", type="password", required="true", description="Password of User"),
     * })
     * @return Response
     */
    public function authenticate()
    {
        request()->validate([
            'email' => 'required|email',
            'password' => 'required'
        ],[],[
            'email' => trans('auth.email'),
            'password' => trans('auth.password'),
        ]);

        // login throttle check

        $ip = getRemoteIPAddress();

        if(config('config.login_throttle') &&  Cache::has($ip) && Cache::has('last_login_attempt') && Cache::get($ip) >= config('config.login_throttle_attempt')){
            $last_login_attempt = Cache::get('last_login_attempt');
            $throttle_timeout = Carbon::parse($last_login_attempt)->addMinutes(config('config.login_throttle_timeout'))->toDateTimeString();
            if($throttle_timeout >= Carbon::now()->toDateTimeString())
                return $this->error(['email' => trans('auth.login_throttle_limit_crossed',['time' => showTime($throttle_timeout)])]);
            else {
                Cache::forget($ip);
                Cache::forget('last_login_attempt');
            }
        }

        $credentials = request()->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {

                if(config('config.login_throttle')){
                    if (Cache::has($ip))
                        $throttle_attempt = Cache::get($ip) + 1;
                    else
                        $throttle_attempt = 1;
                    cache([$ip => $throttle_attempt], 300);
                    cache(['last_login_attempt' => Carbon::now()->toDateTimeString()], 300);
                }

                return $this->error(['email' => trans('auth.failed')]);
            }
        } catch (JWTException $e) {
            return $this->error(['email' => trans('general.something_wrong')]);
        }

        Cache::forget($ip);
        Cache::forget('last_login_attempt');

        $user = User::whereEmail(request('email'))->first();

        // User status check

        if($user->status == 'pending_activation')
            return $this->error(['email' => trans('auth.pending_activation')]);

        if($user->status == 'pending_approval')
            return $this->error(['email' => trans('auth.pending_approval')]);

        if($user->status == 'disapproved')
            return $this->error(['email' => trans('auth.not_activated')]);

        if($user->status == 'banned')
            return $this->error(['email' => trans('auth.account_banned')]);

        if($user->status != 'activated')
            return $this->error(['email' => trans('auth.not_activated')]);

        // if(!$user->hasPermissionTo('enable-login'))
        //     return $this->error(['email' => trans('auth.login_permission_disabled')]);

        // // Two factor auth check

        // event(new UserLogin($user));

        // if(config('config.two_factor_security')){
        //     $two_factor_code = rand(100000,999999);
        //     $user->notify(new TwoFactorSecurity($two_factor_code));
        // }
        // else
        //     $two_factor_code = '';

        $this->logActivity($user, ['module' => $this->module, 'module_id' => $user->id, 'user_id' => $user->id, 'activity' => 'logged_in', 'message' => 'logged_in']);

        $deviceToken = $this->getValueFromRequest('deviceToken');
        $deviceName = $this->getValueFromRequest('deviceName');
        $devicePlatform = $this->getValueFromRequest('devicePlatform');
        $this->handleDeviceToken($user, $deviceToken, $devicePlatform, $deviceName);

        return $this->success(['message' => trans('auth.logged_in'),'token' => $token, 'user' => $user]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $token = $this->guard()->attempt($this->credentials($request));

        if (! $token) {
            return false;
        }

        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return false;
        }

        $this->guard()->setToken($token);

        return true;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        $token = (string) $this->guard()->getToken();
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration - time(),
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            throw VerifyEmailException::forUser($user);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $auth_user = $this->getAuthUser();
        try {
            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

        } catch (JWTException $e) {
            return $this->error($e->getMessage());
        }

        $this->logActivity($auth_user, ['module' => $this->module, 'module_id' => $auth_user->id, 'user_id' => $auth_user->id, 'activity' => 'logged_out', 'message' => 'logged_out']);

        return $this->success(['message' => trans('auth.logged_out')]);
    }
}
