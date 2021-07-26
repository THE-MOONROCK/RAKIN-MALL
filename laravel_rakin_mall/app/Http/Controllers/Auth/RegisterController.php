<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

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
     * Used to create user
     * @post ("/api/register")
     * @param ({
     *      @Parameter("first_name", type="text", required="true", description="First Name of User"),
     *      @Parameter("last_name", type="text", required="true", description="Last Name of User"),
     *      @Parameter("email", type="email", required="true", description="Email of User"),
     *      @Parameter("password", type="password", required="true", description="Password of User"),
     *      @Parameter("password_confirmation", type="password", required="true", description="Confirm Password of User"),
     *      @Parameter("tnc", type="checkbox", required="optional", description="Accept Terms & Conditions"),
     * })
     * @return Response
     */

    public function register()
    {
        // if(!config('config.registration'))
        // return $this->error(['message' => trans('general.feature_not_available')]);
        
        $validation_rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ];

        $friendly_names = [
            'first_name' => trans('auth.first_name'),
            'last_name' => trans('auth.last_name'),
            'email' => trans('auth.email'),
            'password' => trans('auth.password'),
            'password_confirmation' => trans('auth.password_confirmation'),
        ];

        request()->validate($validation_rules,[],$friendly_names);

        $user_count = User::count();

        // if($user_count){
        //     $default_designation = Designation::whereIsDefault(1)->first();

        //     if(!$default_designation)
        //         return $this->error(['message' => trans('auth.no_default_designation')]);
        // }
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => request('first_name') .' '. request('last_name'),
                'email' => request('email'),
                'status' => 'pending_activation',
                'password' => bcrypt(request('password'))
            ]);
    
            if(config('config.email_verification'))
                $status = 'pending_activation';
            elseif(config('config.account_approval'))
                $status = 'pending_approval';
            else
                $status = 'activated';
    
            $user->uuid = generateUuid();
            $user->activation_token = generateUuid();
            $user->status = $status;
            $user->save();
            // $user->assignRole(($user_count) ? config('system.default_role.user') : config('system.default_role.admin'));
            $profile = new Profile;
            // $profile->dest_id = ($user_count) ? $default_designation->id : null;
            $profile->first_name = request('first_name');
            $profile->last_name = request('last_name');
            $user->profile()->save($profile);
            $user_preference = new UserPreference;
            $user->userPreference()->save($user_preference);
    
            // if(config('config.email_verification'))
            //     $user->notify(new Activation($user));
            DB::commit();
            return $this->success(['message' => trans('auth.account_created')]);
        } catch (\Exception $ex) {
            DB::rollback();
            return ['message' => trans('auth.account_not_created')];
        }
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    protected function registered(Request $request, User $user)
    {
        if ($user instanceof MustVerifyEmail) {
            return response()->json(['status' => trans('verification.sent')]);
        }

        return response()->json($user);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email:filter|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
