<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    /* To update the primary tags */
    public static function updateVendorPrimaryTags($vendorId, $primaryTags)
    {
        $updateVendor = Vendors::find($vendorId);
        $updateVendor->primary_tags = $primaryTags;
        $updateVendor->save();
        return $updateVendor;
    }

    public static function getTotalVendors()
    {
        $vendors = Vendors::select('id','shop_name_en')->get();
        return $vendors;
    }
}
