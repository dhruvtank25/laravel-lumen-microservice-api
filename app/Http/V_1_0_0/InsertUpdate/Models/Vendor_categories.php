<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor_categories extends Model
{
    public static function getVendorCategoryDetails($categoryName)
    {
        $vendorCategories = Vendor_categories::select('id','name_en','name_ar')->where('slug', '=', Str::slug($categoryName));
        return $vendorCategories->first();
    }
}
