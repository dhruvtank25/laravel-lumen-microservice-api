<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Drivers extends Model
{
    /* To get the authentication details */
    public static function getAuthenticationDetails($driver_id)
    {
        $authentication = Drivers::select('authentication_id')->where('id', $driver_id);
        return $authentication->first();
    }

    public static function getAllDrivers()
    {
        $drivers = Drivers::select('id','authentication_id')->where('status','=','1')->where('verified','=','1');
        return $drivers->get();
    }
    /* To get the driver device detail */
    public static function getAllDriversDeviceDetail()
    {
        $drivers = DB::select("select authentication_devices.id, authentication_devices.authentication_id, drivers.id as driver_id, authentication_devices.fcm_token, authentication_devices.device_type from authentication_devices join (
            select authentication_id, max(updated_at) as max_dt, status
            from authentication_devices where status = '1' and fcm_token is not null and fcm_token <> '' and device_type is not null and device_type <> ''
            group by authentication_id
        ) t on authentication_devices.authentication_id= t.authentication_id and authentication_devices.updated_at = t.max_dt 
        join drivers on drivers.authentication_id = t.authentication_id
        where drivers.status = '1' order by authentication_devices.id desc");
        return $drivers;
    }
    /* to get the one click driver detail */
    public static function getOneClickDriverDetail()
    {
        return Drivers::select('id','authentication_id','firstname','lastname','email','phone','country_code','akeed_driver_id')->where('one_click_driver','=','Y')->first();
    }

}
