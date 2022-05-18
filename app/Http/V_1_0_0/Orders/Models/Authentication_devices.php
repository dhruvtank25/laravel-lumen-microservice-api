<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Authentication_devices extends Model
{
    /* To get the authentication detail */
    public static function getAuthenticationDeviceDetails($authentication_id)
    {
        $authentication = Authentication_devices::select(DB::raw('distinct(authentication_id)'), 'id', 'fcm_token','device_type')->where('authentication_id', $authentication_id)->where('status','=','1')->whereNotNull('fcm_token')->whereNotNull('device_type')->orderby('id','desc');
        return $authentication ->first();
    }
}
