<?php

namespace App\Http\V_1_0_0\Migration\Models;

use Illuminate\Database\Eloquent\Model;

class Authentications extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'phone', 'country_code', 'role', 'firstname', 'lastname', 'firstname_ar', 'lastname_ar', 'akeed_user_id', 'plain_password', 'created_at', 'updated_at'
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
        $authentications->firstname = $input['firstname'];
        $authentications->firstname_ar = $input['firstname_ar'];
        $authentications->lastname = $input['lastname'];
        $authentications->lastname_ar = $input['lastname_ar'];
        $authentications->email = $input['email'];
        $authentications->phone = $input['phone'];
        $authentications->password = $input['password'];
        $authentications->plain_password = $input['plain_password'];
        $authentications->role = $input['role'];
        $authentications->akeed_user_id = $input['akeed_user_id'];
        $authentications->created_at = $input['created_at'];
        $authentications->updated_at = $input['updated_at'];
        $authentications->save();
        return $authentications->id;
    }
    /* To check the authentication is already exist or not */
    public static function checkAuthenticationVendor($akeed_user_id)
    {
        return Authentications::select('id')->where('akeed_user_id',$akeed_user_id)->where('role','=',2)->first();
    }
    /* To update the authentication */
    public static function update_Authentication($input, $authentication_id)
    {
        $authentications = Authentications::find($authentication_id);
        if (!empty($authentications)) {
            $authentications->firstname = $input['firstname'];
            $authentications->firstname_ar = $input['firstname_ar'];
            $authentications->lastname = $input['lastname'];
            $authentications->lastname_ar = $input['lastname_ar'];
            $authentications->email = $input['email'];
            $authentications->phone = $input['phone'];
            $authentications->password = $input['password'];
            $authentications->plain_password = $input['plain_password'];
            $authentications->role = $input['role'];
            $authentications->akeed_user_id = $input['akeed_user_id'];
            $authentications->created_at = $input['created_at'];
            $authentications->updated_at = $input['updated_at'];
            $authentications->save();
            return $authentications->id;
        }
        return true;
    }
}
