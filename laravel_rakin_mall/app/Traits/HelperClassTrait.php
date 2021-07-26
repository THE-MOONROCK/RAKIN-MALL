<?php

/**
 * Trait used for common functions
 */
namespace App\Traits;

use App\Models\DeviceToken;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

trait HelperClassTrait {

    /**
     * Used to get authenticated user instance
     * @return Response
     */

    public function getAuthUser(){
        try {
            $auth_user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            $auth_user = null;
        }

        return $auth_user;
    }

    /**
     * @deprecated
     * Used to get log activity
     * @param ({
     *      @Parameter("data", type="array", required="true", description="Array of user_id, module, module_id, sub_module, sub_module_id etc"),
     * })
     * @return void
     */
    public function logActivity($model, $data) {
        $auth_user = getAuthUser();
        $data['user_id'] = isset($data['user_id']) ? $data['user_id'] : ($auth_user ? $auth_user->id : null);
        $data['ip'] = getClientIp();
        $data['module'] = isset($data['module']) ? $data['module'] : null;
        $data['module_id'] = isset($data['module_id']) ? $data['module_id'] : null;
        $data['sub_module'] = isset($data['sub_module']) ? $data['sub_module'] : null;
        $data['sub_module_id'] = isset($data['sub_module_id']) ? $data['sub_module_id'] : null;
        $data['key'] = isset($data['key']) ? $data['key'] : null;
        $data['value_from'] = isset($data['value_from']) ? $data['value_from'] : null;
        $data['value_to'] = isset($data['value_to']) ? $data['value_to'] : null;
        $data['message'] = isset($data['message']) ? $data['message'] : null;
        $data['user_agent'] = Request::header('User-Agent');

        activity()
            ->performedOn($model)
            ->withProperties($data)
            ->log($data['message']);
    }

    private function getChild($child){
        $children = [];
        foreach ($child as $value){
            if($value->children && sizeof($value->children) > 0){
                foreach ($value->children as $row){
                    $children[] = $row->id;
                }
            }
        }
        return $children;
    }

    /**
     * Do after form was has been saved
     */
    public function afterSaveUpload($module,$token) {
        $uploads = Upload::whereModule($module)
                    ->whereUploadToken($token)
                    ->get();
        foreach($uploads as $upload) {
            if ($upload->is_temp_delete) {
                $upload->getFirstMedia('file')->delete();
                $upload->whereId($upload->id)->delete();
            } else if(!$upload->status){
                $upload->update(['status' => true]);
            }
        }
    }

    /**
     * Used to get default Company Address
     * @return string
     */

    public function getCompanyAddress(){
        $address = config('config.address_line_1');
        $address .= (config('config.address_line_2')) ? (', <br >'.config('config.address_line_2')) : '';
        $address .= (config('config.city')) ? ', <br >'.(config('config.city')) : '';
        $address .= (config('config.state')) ? ', '.(config('config.state')) : '';
        $address .= (config('config.zipcode')) ? ', '.(config('config.zipcode')) : '';
        $address .= (config('config.country_id')) ? '<br >'.(config('config.country')) : '';

        return $address;
    }

    public function proceedWithInstallation($data){
        $url = "https://scriptmint.com/api/v1/purchase/verification";
        $postData = array(
            'product_code' => '870303',
            'url' => Request::url(),
            'license' => $data['license'],
            'email' => $data['email']
        );
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response,true);
    }

    public function checkIfRequestValid($key) {
        if (!$key) return false;
        if (!request()->has($key)) return false;
        $value = request($key);
        if (!$value || $value == '') return false;
        if (!isset($value)) return false;
        return true;
    }

    public function getValueFromRequest($key, $default = null) {
        if ($this->checkIfRequestValid($key)) {
            return request($key);
        }
        if ($this->checkIfRequestValid(strtolower($key))) {
            return request(strtolower($key));
        }
        return $default ? $default : null;
    }

    /**
     * @param $user
     * @param $deviceToken
     * @param $devicePlatform
     * @param int $pushType
     * @param int $inActive
     * @return null
     */
    public function handleDeviceToken($user, $deviceToken, $devicePlatform, $deviceName=null, $pushType=0, $inActive = 0) {
        if ($deviceToken) {
            DB::table("device_token")->where('user_id', $user->id)->where('in_active', 0)->update(array('in_active' => 1));

            $userDeviceToken = DeviceToken::where('token', $deviceToken)->where('user_id', $user->id)->first();
            if (!$userDeviceToken)
                $userDeviceToken = new DeviceToken;

            $userDeviceToken->user_id = $user->id;
            $userDeviceToken->token = $deviceToken;
            $userDeviceToken->platform = $devicePlatform;
            $userDeviceToken->name = $deviceName;
            $userDeviceToken->type = $pushType;
            $userDeviceToken->in_active = $inActive;
            $userDeviceToken->save();
            return $devicePlatform;
        }

        return null;
    }

    public function queryDefaultSort($list) {
        $sortBy = $this->getValueFromRequest('sortBy', 'created_at');
        $sortOrder = $this->getValueFromRequest('order', 'desc');
        return $list->orderBy($sortBy, $sortOrder);
    }
}