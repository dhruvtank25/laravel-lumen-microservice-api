<?php

namespace App\Http\V_1_0_0\Profile\Models;

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

    public static function update_authentication($input)
    {
        $authentications = Authentications::where('id', "=", $input['authentication_id'])->first();
        $authentications->firstname = $input['shop_name_en'];
        $authentications->firstname_ar = $input['shop_name_ar'];
        $authentications->save();
        return $authentications->first();
    }

}
