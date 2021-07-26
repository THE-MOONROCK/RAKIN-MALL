<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return ['status' => trans($response)];
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['email' => trans($response)], 400);
    }

    /**
     * Used to validate user password
     * @post ("/api/password/validate-password-reset")
     * @param ({
     *      @Parameter("token", type="string", required="true", description="Reset Password Token"),
     * })
     * @return Response
     */

    public function validatePasswordReset(){
        $validate_password_request = DB::table('password_resets')->where('token','=',request('token'))->first();

        if(!$validate_password_request)
            return $this->error(['message' => trans('passwords.token')]);

        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+".config('config.reset_password_token_lifetime')." minutes")) < date('Y-m-d H:i:s'))
            return $this->error(['message' => trans('passwords.token_expired')]);

        return $this->success(['message' => '']);
    }

    /**
     * Used to reset user password
     * @post ("/api/password/reset")
     * @param ({
     *      @Parameter("token", type="string", required="true", description="Reset Password Token"),
     *      @Parameter("email", type="email", required="true", description="Email of User"),
     *      @Parameter("password", type="password", required="true", description="New Password of User"),
     *      @Parameter("password_confirmation", type="password", required="true", description="New Confirm Password of User"),
     * })
     * @return Response
     */

    public function reset(){
        // if(!config('config.reset_password'))
        //     return $this->error(['message' => trans('general.feature_not_available')]);

        request()->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ],[],[
            'email' => trans('auth.email'),
            'password' => trans('auth.password'),
            'password_confirmation' => trans('auth.password_confirmation'),
        ]);

        $user = User::whereEmail(request('email'))->first();

        if(!$user)
            return $this->error(['email' => trans('passwords.user')]);

        if($user->status != 'activated')
            return $this->error(['email' => trans('passwords.account_not_activated')]);

        $validate_password_request = DB::table('password_resets')->where('email','=',request('email'))->where('token','=',request('token'))->first();

        if(!$validate_password_request)
            return $this->error(['email' => trans('passwords.token')]);

        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+".config('config.reset_password_token_lifetime')." minutes")) < date('Y-m-d H:i:s'))
            return $this->error(['email' => trans('passwords.token_expired')]);

        $user->password = bcrypt(request('password'));
        $user->save();
        DB::table('password_resets')->where('email','=',request('email'))->where('token','=',request('token'))->delete();

        // $user->notify(new PasswordResetted($user));

        return $this->success(['message' => trans('passwords.reset')]);
    }
}
