<?php

namespace App\Http\V_1_0_0\Authentications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Http\V_1_0_0\Authentications\Models\Authentication_devices;

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

    /* To check the login credential is valid or not */
    public static function getLoginDetails($user_type, $email, $mobile_number)
    {
        $checkLogin = Authentications::select('authentications.id as authentication_id', 'authentications.email', 'v.shop_name_en as FirstName', 'v.shop_name_ar as ARFirstName', 'status as IsActive', 'v.id as VendorId',  'authentications.phone', 'v.logo', 'v.vendor_category_en as RESTURANTSCATEGORY', 'authentications.country_code', 'akeed_user_id', 'password')
            ->join('vendors as v','v.authentication_id', '=', 'authentications.id')->where('role', $user_type);
        if (!empty($email)) {
            $checkLogin = $checkLogin->where('authentications.email', '=', $email);
        }
        if (!empty($mobile_number)) {
            $checkLogin = $checkLogin->where('authentications.phone', '=', $mobile_number);
        }
        return $checkLogin->first();
    }


    public static function logout($input)
    {
        if ($input['logout_from_all'] == 1) {
            $authentications = Authentications::find($input['authentication_id']);
            $authentications->login_status = '0';
            $authentications->save();
            Authentication_devices::where('authentication_id', $input['authentication_id'])->update(['status' => '0']);
        } else {
            $authentications = Authentication_devices::find($input['authentication_device_id']);
            $authentications->status = '0';
            $authentications->save();
        }
        return true;
    }

    public static function changePassword($authentication_id,$password)
    {
        $changePwd = Authentications::where('id', "=", $authentication_id)->first();
        if ($changePwd)
        {
            $hashed_password = Hash::make($password);
            $changePwd->password =$hashed_password;
            $changePwd->plain_password =$password;
            $changePwd->save();
        }

        return $changePwd;

    }


}
