<?php

namespace App\Http\V_1_0_0\Migration\Models;

use Illuminate\Database\Eloquent\Model;


class Authentication_devices extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authentication_id', 'device_id', 'otp', 'latitude', 'longitude', 'imei_number', 'fcm_token', 'created_at', 'updated_at'
    ];

    public static function Insert_devices($input)
    {
        $authentication_devices = new Authentication_devices;
        $authentication_devices->authentication_id = $input['authentication_id'];
        $authentication_devices->otp = $input['otp'];
        $authentication_devices->latitude = $input['latitude'];
        $authentication_devices->longitude = $input['longitude'];
        $authentication_devices->imei_number = $input['imei_number'];
        $authentication_devices->fcm_token = $input['fcm_token'];
        $authentication_devices->created_at = $input['created_at'];
        $authentication_devices->updated_at = $input['updated_at'];
        $authentication_devices->save();
        return $authentication_devices->id;
    }
}
