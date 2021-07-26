<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Models\Upload;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function __construct()
    {
        $this->module = 'upload';
        $this->modelClass = Upload::class;
    }

    public function getAllowedExtension()
    {
        if(config('upload.'.request('module').'.allowed_file_extensions'))
            return config('upload.'.request('module').'.allowed_file_extensions');

        return [];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fetch($id)
    {
        Upload::whereModule(request('module'))->whereModuleId($id)->update(['is_temp_delete' => false]);
        $upload = Upload::whereModule(request('module'))->whereModuleId($id)->whereStatus(true)->get();
        return UploadResource::collection($upload);
    }

    /**
     * Used to upload a file
     * @post ("/api/upload")
     * @param ({
     *      @Parameter("module", type="string", required="true", description="Name of module"),
     *      @Parameter("token", type="string", required="true", description="Upload Token from Form"),
     *      @Parameter("file", type="file", required="true", description="File to be uploaded"),
     * })
     * @return Response
     */

    public function upload()
    {
        $module = request('module');
        $token = request('token');

        $config = config('upload.'.$module);
        $auth_required = array_key_exists('auth_required',$config) ? $config['auth_required'] : 1;
        $max_file_size = array_key_exists('max_file_size',$config) ? $config['max_file_size'] : 10000;
        $allowed_file_extensions = array_key_exists('allowed_file_extensions',$config) ? $config['allowed_file_extensions'] : ['jpg','png','jpeg','pdf','doc','docx','xls','xlsx'];
        $max_no_of_files = array_key_exists('max_no_of_files',$config) ? $config['max_no_of_files'] : 5;

        if(!$module || !$token)
            return $this->error(['message' => trans('general.invalid_action') . ' (module/token missing)']);

        if(Upload::whereUploadToken(request('token'))->where('module','!=',$module)->count())
            return $this->error(['message' => trans('general.invalid_action') . ' (token has been used)']);

        try {
            $user = JWTAuth::parseToken()->authenticate();
        }
        catch(JWTException $e){

        }

        if($auth_required && !isset($user))
            return $this->error(['message' => trans('upload.authentication_require_before_upload')]);

        $size = request()->file('file')->getSize();

        if($size > $max_file_size*1024*1024)
            return $this->error(['message' => trans('upload.file_size_exceeds')]);

        if(request()->file('file') == "")
            return $this->error(['message' => trans('upload.not_found')]);

        try {
            $extension = request()->file('file')->extension();
        } catch (Exception $ex) {
            return $this->error($ex->getMessage());
        }

        if(!in_array($extension, $allowed_file_extensions))
            return $this->error(['message' => trans('upload.invalid_extension',['extension' => $extension])]);

        $existing_upload = Upload::whereModule($module)->whereUploadToken($token)->whereIsTempDelete(false)->count();

        if($existing_upload >= $max_no_of_files)
            return $this->error(['message' => trans('upload.max_file_limit_crossed',['number' => $max_no_of_files])]);

        $upload = Upload::create();
        $upload->module = $module;
        $upload->module_id = request('module_id') ? request('module_id') : null;
        $upload->upload_token = $token;

        $originfilename = request()->file('file')->getClientOriginalName();
        $extension = request()->file('file')->getClientOriginalExtension();
        $ascii = mb_detect_encoding($originfilename, 'ASCII', true);
        $formatname = date('ymjHisv') . '-';
        
        if($ascii) {
            $formatname .= $originfilename;
        } else {
            $formatname .= Str::random(40) .".".$extension;
        }

        $upload->user_filename = $originfilename;

        if (request()->file('file')) {
            $upload->addMedia(request()->file('file'))->toMediaCollection('file');
        }
        $upload->filename = $formatname;

        $upload->uuid = generateUuid();
        $upload->user_id = isset($user) ? $user->id : null;
        $upload->save();

        return $this->success(['message' => trans('upload.file_uploaded'),'upload' => $upload]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $upload = Upload::find($id);

        if(!$upload || $upload->upload_token != request('token'))
            return $this->error(['message' => 'Invalid action!']);
        // temporary delete file
        if(request('module_id') && $upload->status)
            $upload->update(['is_temp_delete' => 1]);
        else {
            // delete file
            $upload->getFirstMedia('file')->delete();
            $upload->whereId($id)->delete();
        }

        return $this->success(['message' => trans('upload.file_removed')]);
    }
}