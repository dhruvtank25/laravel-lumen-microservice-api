<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Authentications extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'phone', 'country_code', 'role', 'firstname', 'lastname', 'akeed_user_id', 'plain_password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public static function Insert_authentication($input)
    {
        $authentications = new Authentications;
        $authentications->firstname = $input['FirstName'];
        $authentications->lastname = $input['LastName'];
        $authentications->email = $input['EmailId'];
        $authentications->phone = $input['MobileNo'];
        $hashed_password = Hash::make($input['Password']);
        $authentications->password = $hashed_password;
        $authentications->plain_password = $input['Password'];
        $authentications->role = 2;//2-vendor
        $authentications->akeed_user_id = isset($input['akeedid'])?$input['akeedid']:0;
        $authentications->save();
        return $authentications->id;
    }

    public static function update_authentication($input)
    {
        $authentications = Authentications::where('akeed_user_id', "=", $input['akeedID'])->first();
        if (!empty($authentications)) {
            $authentications->firstname = $input['FirstName'];
            $authentications->lastname = $input['LastName'];
            $authentications->email = $input['EmailId'];
            $authentications->phone = $input['MobileNo'];
            $hashed_password = Hash::make($input['Password']);
            $authentications->password = $hashed_password;
            $authentications->plain_password = $input['Password'];
            // $authentications->akeed_user_id = isset($input['akeedid'])?$input['akeedid']:0;
            $authentications->save();
            return $authentications;
        } else {
            return 0;
        }
    }

    public static function getAuthenticationDetails($email)
    {
        $vendor = Authentications::select('id')->where('email',  $email);
        return $vendor->first();
    }

}
