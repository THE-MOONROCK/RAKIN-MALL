<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends AbstractController
{
    /**
     * UserController constructor.
     */

    public function __construct()
    {
        $this->module = 'user';
        $this->modelClass = User::class;

        $this->buildPermission($this->module);
        $this->dbRelations = [];
        
        $this->validation = [[
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'actor_id' => 'required',
        ],[],[
            'name' => trans('user.name'),
            'email' => trans('user.email'),
            'password' => trans('user.password'),
            'actor_id' => trans('actor.actor')
        ]];

        $auth_user = $this->getAuthUser();
        if ($auth_user && $auth_user->id) {
            $this->currentUser = User::whereId($auth_user->id)->first();
        }
    }
    /**
     * Used to get Pre Requisite for User Module
     * @get ("/api/user/pre-requisite")
     * @return Response
     */

    public function preRequisite()
    {

    }
    /**
     * Used to get all User
     * @get ("/api/user")
     * @return Response
     */

    public function index()
    {

    }
    /**
     * Used to store User
     * @post ("/api/user")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of User"),
     * })
     * @return Response
     */

    public function store(Request $request)
    {

    }
    /**
     * Used to get User detail
     * @get ("/api/user/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     * })
     * @return Response
     */

    public function show($id)
    {

    }
    /**
     * Used to update User
     * @patch ("/api/user/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     * })
     * @return Response
     */

    public function update(Request $request, $id)
    {

    }
    /**
     * Used to delete User
     * @delete ("/api/user/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Uuid of User"),
     * })
     * @return Response
     */

    public function destroy($id)
    {

    }
    //Implement method
    public function fillCreate($model, $request=null)
    {

    }
    public function fillUpdate($model, $request=null)
    {

    }
    public function beforeSave($model, $request=null, $id=null)
    {

    }
    public function afterSave($model, $request=null, $id=null)
    {
        
    }
}
