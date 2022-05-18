<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    public static function getAuthenticationDetails($vendor_id)
    {
        $authentication = Vendors::select('authentication_id', 'firstname', 'lastname')->where('id', $vendor_id);
        return $authentication ->first();
    }
}
