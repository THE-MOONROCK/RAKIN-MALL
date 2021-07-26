<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HelperClassTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    use HelperClassTrait;
    protected $module = 'user';
    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password),
        ]);
    }

    /**
     * Used to change user password
     * @post ("/api/password/change-password")
     * @param ({
     *      @Parameter("current_password", type="password", required="true", description="Current Password of User"),
     *      @Parameter("new_password", type="password", required="true", description="New Password of User"),
     *      @Parameter("new_password_confirmation", type="password", required="true", description="New Confirm Password of User"),
     * })
     * @return Response
     */

    public function changePassword(){
        request()->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|different:current_password|min:6',
            'new_password_confirmation' => 'required|same:new_password'
        ],[],[
            'current_password' => trans('auth.current_password'),
            'new_password' => trans('auth.new_password'),
            'new_password_confirmation' => trans('auth.new_password_confirmation'),
        ]);

        $auth_user = $this->getAuthUser();

        if(!Hash::check(request('current_password'),$auth_user->password))
            return $this->error(['current_password' => trans('passwords.password_mismatch')]);

        if(isTestMode())
            // return $this->error(['message' => trans('general.permission_denied_test_mode')]);

        $auth_user->password = bcrypt(request('new_password'));
        $auth_user->save();

        $this->logActivity($auth_user, ['module' => $this->module, 'module_id' => $auth_user->id, 'sub_module' => 'password', 'activity' => 'resetted', 'message' => 'resetted']);

        return $this->success(['message' => trans('passwords.change')]);
    }

    /**
     * Used to request password reset token for user
     * @post ("/api/password/token")
     * @param ({
     *      @Parameter("email", type="email", required="true", description="Registered Email of User"),
     * })
     * @return Response
     */

    public function password(){
        // if(!config('config.reset_password'))
        //     return $this->error(['message' => trans('general.feature_not_available')]);

        request()->validate([
            'email' => 'required|email'
        ],[],[
            'email' => trans('auth.email')
        ]);

        $user = User::whereEmail(request('email'))->first();

        if(!$user)
            return $this->error(['email' => trans('passwords.user')]);

        if($user->status != 'activated')
            return $this->error(['email' => trans('passwords.account_not_activated')]);

        $token = generateUuid();
        DB::table('password_resets')->insert([
            'email' => request('email'),
            'token' => $token
        ]);
        // $user->notify(new PasswordReset($user,$token));

        return $this->success(['message' => trans('passwords.sent')]);
    }
}
