<?php

namespace App\Http\V_1_0_0\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor_category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_en', 'name_ar', 'image', 'active'
    ];


    public static function getVendorCategory()
    {
        $vendor = Vendor_category::select('id as category_id', 'name_en','name_ar');
        return $vendor->get();

    }

}
