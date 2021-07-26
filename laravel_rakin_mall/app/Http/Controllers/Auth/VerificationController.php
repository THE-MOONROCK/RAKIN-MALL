<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HelperClassTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    use HelperClassTrait;
    protected $module = 'user';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

        /**
     * Used to activate new user
     * @get ("/api/email/activate/{token}")
     * @param ({
     *      @Parameter("token", type="string", required="true", description="Activation Token of User"),
     * })
     * @return Response
     */

    public function activate($activation_token){
        // if(!config('config.registration') || !config('config.email_verification'))
        //     return $this->error(['message' => trans('general.feature_not_available')]);

        $user = User::whereActivationToken($activation_token)->first();

        if(!$user)
            return $this->error(['message' => trans('auth.invalid_token')]);

        if($user->status == 'activated')
            return $this->error(['message' => trans('auth.account_already_activated')]);

        if($user->status != 'pending_activation')
            return $this->error(['message' => trans('auth.invalid_token')]);

        $user->status = (config('config.account_approval') ? 'pending_approval' : 'activated');
        $user->save();
        // $user->notify(new Activated($user));

        return $this->success(['message' => trans('auth.account_activated')]);
    }

    /**
     * Used to verify password during Screen Lock
     * @post ("/api/password/lock")
     * @param ({
     *      @Parameter("password", type="password", required="true", description="Password of User"),
     * })
     * @return Response
     */

    public function lock(){
        $auth_user = $this->getAuthUser();

        request()->validate([
            'password' => 'required'
        ],[],[
            'password' => trans('auth.password')
        ]);

        if(!Hash::check(request('password'),$auth_user->password))
            return $this->error(['password' => trans('passwords.lock_screen_password_mismatch')]);

        $this->logActivity($auth_user, ['module' => $this->module, 'module_id' => $auth_user->id, 'sub_module' => 'screen', 'activity' => 'unlocked', 'message' => 'unlocked']);

        return $this->success(['message' => trans('auth.lock_screen_verified')]);
    }

    /**
     * Mark the user's email address as verified.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, User $user)
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'status' => trans('verification.invalid'),
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => trans('verification.already_verified'),
            ], 400);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json([
            'status' => trans('verification.verified'),
        ]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            throw ValidationException::withMessages([
                'email' => [trans('verification.user')],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [trans('verification.already_verified')],
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => trans('verification.sent')]);
    }
}
