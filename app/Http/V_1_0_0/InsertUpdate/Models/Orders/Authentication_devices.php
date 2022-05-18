<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use DB;

class Authentication_devices extends Model
{
    /* To authentication device detail */
    public static function authenticationDeviceDetail($authentication_id, $authentication_device_id = '')
    {
        $authentication = Authentication_devices::select(DB::raw('distinct(authentication_id)'), 'id', 'fcm_token','device_type')->where('authentication_id', $authentication_id)->where('status','=','1')->whereNotNull('fcm_token')->whereNotNull('device_type')->orderby('id','desc');
        if (!empty($authentication_device_id)) {
            $authentication = $authentication->where('id',$authentication_device_id);
        }
        return $authentication ->first();
    }
    /* To check the login credential is valid or not */
    public static function Insert_devices($input)
    {
        $authentication_devices = new Authentication_devices;
        $authentication_devices->authentication_id = $input['customer_authentication_id'];
        $authentication_devices->status = '1';
        $authentication_devices->latitude = isset($input['lat'])?$input['lat']:'';
        $authentication_devices->longitude = isset($input['longi'])?$input['longi']:'';
        $authentication_devices->save();
        return $authentication_devices->id;
    }
}
