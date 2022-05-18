<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    /* To get the vendor details using akeed vendor id */
    public static function getVendorDetails($vendor_id)
    {
        return Vendors::select('id', 'authentication_id', 'shop_name_en', 'shop_name_ar', 'address_1', 'landmark', 'latitude', 'longitude', 'phone', 'email','one_click_vendor','municipal_tax', 'service_charge', 'discount_type', 'min_order_discount', 'discount_active_from', 'discount_active_to', 'discount_amount', 'discount_percentage', 'akeed_percentage')->where('akeed_vendor_id', '=', $vendor_id)->first();
    }
}
