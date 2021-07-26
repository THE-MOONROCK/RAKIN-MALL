<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\PermissionChecker;
use App\Traits\HelperClassTrait;
use Illuminate\Support\Facades\Log;

abstract class AbstractController extends Controller
{
    use HelperClassTrait;
    use PermissionChecker;

    protected $module;
    protected $typeProject;
    protected $modelClass;
    protected $typeRecordManage = false;
    protected $softDelete = false;
    protected $useUUID = false;
    protected $model;
    protected $validation;
    protected $dbRelations;
    protected $listRelations;
    protected $messages;
    protected $currentUser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!$this->canList())
            return $this->error(['message' => trans('general.permission_denied')]);

        /**
         * $this->dbRelations = array('goal','user','designation','project','project_classifier','project_type')
         */
        if ($this->dbRelations && is_array($this->dbRelations) && sizeof($this->dbRelations) > 0) {
            $list = $this->modelClass::with($this->dbRelations)->whereNotNull('id');
        } else {
            $list = $this->modelClass::whereNotNull('id');
        }

        $this->searchFilter($list);

        return $this->listIt($list);
    }

    protected function listIt($list) {
        return $list->paginate(request('pageLength'));
    }

//    protected function setup() {
//        if ($this->dbRelations && is_array($this->dbRelations)
//            && sizeof($this->dbRelations) > 0
//            && $this->listRelations && is_array($this->listRelations)
//            && sizeof($this->listRelations) == 0) {
//            $this->listRelations = $this->dbRelations;
//        }
//    }

    protected function searchFilter($list) {
        if(request()->has('code'))
            $list->where('code',request('code'));
        if(request()->has('name'))
            $list->where('name','like','%'.request('name').'%');
        if(request()->has('title'))
            $list->where('title','like','%'.request('title').'%');

        if(request()->has('start_date_start') && request()->has('start_date_end'))
            $list->whereBetween('prepare_start_date',[request('start_date_start'),request('start_date_end')]);

        if(request()->has('due_date_start') && request()->has('due_date_end'))
            $list->whereBetween('prepare_end_date',[request('due_date_start'),request('due_date_end')]);

        $this->queryDefaultSort($list);
    }

    /**
     * Selection Option for API of the current model
     */
    protected function options() {
        return null;
    }

    protected function saveOrUpdate(Request $request=null, $id=null, $validation=[], $upload=false) {
        Log::debug("-----: on SaveOrUpdate" . ($id ? ":" . $id : "") . " - " . $this->module);
        $actionUpdate = ($id && ($this->useUUID? $id != null : $id > 0));

        if ($actionUpdate) {
            if(!$this->canEdit()){
                if($this->specialCRUDPermission($id)) {
                } else
                    return $this->error(['message' => trans('general.permission_denied')]);
            }

            if ($this->useUUID) {
                $model = $this->modelClass::whereUuid($id)->first();
            } else {
                $model = $this->modelClass::find($id);
            }

            if(!$model)
                return $this->error(['message' => trans($this->module. '.could_not_find')]);
        } else {
            if(!$this->canCreate()){
                if($this->specialCRUDPermission($id)) {
                }else
                    return $this->error(['message' => trans('general.permission_denied')]);
            }

            $model = new $this->modelClass;
        }

        $this->beforeSave($model, $request, $id);

        if ($validation && sizeof($validation) > 0) {
            if (sizeof($validation) != 3) {
                return $this->error(['message' => trans('general.request_validation_technical')]);
            }
            request()->validate($validation[0], $validation[1], $validation[2]);
        }

        if ($actionUpdate) {
            $model->fill(request()->all());
            $this->fillUpdate($model, $request);

            if (array_key_exists('updated_by', $model->attributesToArray())) {
                $model->updated_by = $this->auth_user->id;
            }
            $model->save();
        } else {
            $model->fill(request()->all());
            $this->fillCreate($model, $request);
            if ($this->useUUID) {
                $model->uuid = generateUuid();
            }
            if (array_key_exists('created_by', $model->attributesToArray())) {
                $model->created_by = $this->auth_user->id;
            }
            $model->save();
        }

        $this->afterSave($model, $request, $id, $actionUpdate);

        if ($upload) {
            $this->afterSaveUpload($this->module, request('upload_token'));
        }

        $this->handleActivityLog($this->module, $model, ($id && $id>0?'updated' : 'added'), $id);

        $this->model = $model;
        $msg = trans($this->module . ($actionUpdate? '.updated':'.added'));
        if ($this->messages) {
            $msg = $this->messages;
        }
        $id = $this->useUUID ? $model->uuid : $model->id;
        return $this->success(['message' => $msg, 'id' => $id]);
    }

    /**
     * @param $module
     * @param $moduleId
     * @param $activity
     * @param null $key
     * @param null $valueFrom
     * @param null $valueTo
     * @param null $message
     * @param null $subModule
     * @param null $subModuleId
     */
    protected function handleActivityLog($module, $model, $activity, $id=null, $key=null, $valueFrom=null, $valueTo=null, $message=null, $subModule = null, $subModuleId = null) {
        $this->logActivity(
            $model, [
            'module' => $module,
            'module_id' => $model->id,
            'sub_module' => $subModule,
            'sub_module_id' => $subModuleId,
            'activity' => $activity,
            'key' => $key,
            'value_from' => $valueFrom,
            'value_to' => $valueTo,
            'message' => $message
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->validation) {
            Log::error('Dear developer, missing implementation on your controller (store) that extended from AbstractController');
            return $this->error(['message' => trans('general.technical_error')]);
        }
        $customMsg = $this->customOnSaveValidation();
        if ($customMsg) {
            return $this->error(['message' => $customMsg]);
        }

        return $this->saveOrUpdate($request, null, $this->validation);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->validation) {
            Log::error('Dear developer, missing implementation on your controller (update) that extended from AbstractController');
            return $this->error(['message' => trans('general.technical_error')]);
        }

        $customMsg = $this->customOnSaveValidation($id);
        if ($customMsg) {
            return $this->error(['message' => $customMsg]);
        }

        return $this->saveOrUpdate($request, $id, $this->validation);
    }

    /**
     * @param $model
     * @param Request $request
     * @return mixed
     */
    abstract protected function fillUpdate($model, $request=null);

    /**
     * Only if need for creation rule
     * @param $model
     * @param Request $request
     */
    protected function fillCreate($model, $request=null) {

    }

    protected function customOnSaveValidation($id=null) {
        return null;
    }

    /**
     * @param $model
     * @param Request $request
     */
    protected function beforeSave($model, $request=null, $id=null) {
    }

    /**
     * @param $model
     * @param Request $request
     */
    protected function afterSave($model, $request=null, $id=null) {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Used to get <model> detail
     * @get ("/api/<model>/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of <model>"),
     * })
     * @return Response
     */
    public function show($id){
        Log::debug("-----: on Show" . ($id ? ":" . $id : "") . " - " . $this->module);
        if(!$this->canList())
            return $this->error(['message' => trans('general.permission_denied')]);

        if ($this->useUUID) {
            $data = $this->modelClass::whereUuid($id)->first();
            if(!$data){
                //For Select
                $data = $this->modelClass::find($id);
            }
        } else {
            $data = $this->modelClass::find($id);
        }

        if(!$data)
            return $this->error(['message' => trans($this->module . '.could_not_find')]);

        $compactResource = $this->performAfterShow($data);
        if ($compactResource) {
            return $this->success($compactResource);
        }
        return $data;
    }

    /**
     * Display the specified resource (after show function)
     */
    protected function performAfterShow($model) {
        return null;
    }

    /**
     * Used to delete <model>
     * @delete ("/api/<model>/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of <model>"),
     * })
     * @return Response
     */
    public function destroy($id) {
        Log::debug("-----: on destroy" . ($id ? ":" . $id : "") . " - " . $this->module);
        if(!$this->canDelete())
            return $this->error(['message' => trans('general.permission_denied')]);

        if ($this->useUUID) {
            $model = $this->modelClass::whereUuid($id)->first();
        } else {
            $model = $this->modelClass::find($id);
        }


        if(!$model)
            return $this->error(['message' => trans($this->module . '.could_not_find')]);

        $arrModel = $model->toArray();
        if(array_key_exists('is_hidden', $arrModel) && $arrModel['is_hidden'] == true)
            return $this->error(['message' => trans($this->module . '.default_cannot_be_deleted')]);

        if (!$this->destroyPermissionRule($model)) {
            return $this->error(['message' => trans('general.permission_denied')]);
        }

        $this->logActivity($model, ['module' => $this->module, 'module_id' => $model->id, 'activity' => 'deleted', 'message' => 'deleted']);

        // $notification = \App\Notification::where('module',$this->module)->where('module_id',$model->id)->delete();

        $model->delete();

        return $this->success(['message' => trans($this->module . '.deleted')]);
    }

    protected function destroyPermissionRule($model) {
        return $model;
    }

    protected function isActionUpdate($id = null) {
        return ($id && ($this->useUUID? $id != null : $id > 0));
    }
}
