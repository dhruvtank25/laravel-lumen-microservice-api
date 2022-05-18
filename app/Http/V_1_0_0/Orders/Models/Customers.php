<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;


class Customers extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authentication_id', 'firstname', 'lastname', 'alternate_mobile', 'alternate_mobile_country_code', 'profile_image', 'gender', 'dob', 'status', 'verified', 'language'
    ];

    public static function getAuthenticationDetails($customer_id)
    {
        $authentication = Customers::select('authentication_id')
            ->where('id', $customer_id);

        return $authentication ->first();
    }

}
