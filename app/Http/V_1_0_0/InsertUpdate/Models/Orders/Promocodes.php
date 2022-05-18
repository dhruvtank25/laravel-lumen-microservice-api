<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Promocodes extends Model
{
    public static function getPromoCodeDetails($promo_code)
    {
        return Promocodes::where('promo_code', '=', $promo_code)->first();
    }
}
