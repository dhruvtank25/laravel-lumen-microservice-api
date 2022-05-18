<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class Authentications extends Model
{
    public static function getRoleDetails($authentication_id)
    {
        $authentication = Authentications::select('role')->where('id', $authentication_id);
        return $authentication ->first();
    }
    /* To get the admin authentication detail */
    public static function getAdminDetail($role)
    {
        return Authentications::select('id')->where('role',$role)->orderby('id','asc')->first();
    }
}
