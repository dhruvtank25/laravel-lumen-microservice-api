<?php

namespace App\Http\V_1_0_0\Authentications\Models;

use Illuminate\Database\Eloquent\Model;

class Authentication_devices extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authentication_id', 'device_id', 'device_name', 'device_version', 'app_version', 'gcm_token', 'fcm_token', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    /* To check the login credential is valid or not */
    public static function Insert_devices($input)
    {
        $authentication_devices = new Authentication_devices;
        $authentication_devices->authentication_id = $input['authentication_id'];
        $authentication_devices->status = strval($input['status']);
        $authentication_devices->device_id = isset($input['device_id'])?$input['device_id']:'';
        $authentication_devices->device_type = isset($input['device_type'])?$input['device_type']:'';
        $authentication_devices->device_name = isset($input['device_name'])?$input['device_name']:'';
        $authentication_devices->device_version = isset($input['device_version'])?$input['device_version']:'';
        $authentication_devices->app_version = isset($input['app_version'])?$input['app_version']:'';
        $authentication_devices->gcm_token = isset($input['gcm_id'])?$input['gcm_id']:'';
        $authentication_devices->fcm_token = $input['fcm_token'];
        $authentication_devices->latitude = isset($input['latitude'])?$input['latitude']:'';
        $authentication_devices->longitude = isset($input['longitude'])?$input['longitude']:'';
        $authentication_devices->imei_number = isset($input['imei_number'])?$input['imei_number']:'';
        $authentication_devices->save();
        return $authentication_devices->id;
    }

}
