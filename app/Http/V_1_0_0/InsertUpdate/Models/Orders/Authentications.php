<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Authentications extends Model
{
    /* To authentication device detail */
    public static function getAdminDetail($role)
    {
        return Authentications::select('id')->where('role', $role)->orderby('id', 'asc')->first();
    }

    public static function insertAuthentication($input)
    {
        $authentications = new Authentications;
        $authentications->firstname = $input['customer_first_name'];
        $authentications->lastname = $input['customer_last_name'];
        $authentications->email = $input['customer_email'];
        $authentications->phone = $input['customer_mobile'];
        $authentications->role = 1;
        $authentications->country_code = '+95';
        $hashed_password = Hash::make($input['customer_password']);
        $authentications->password = $hashed_password;
        $authentications->plain_password = $input['customer_password'];
        $authentications->akeed_user_id = isset($input['customer_id']) ? $input['customer_id'] : 0;
        $authentications->save();
        return $authentications->id;
    }
}
