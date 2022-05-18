<?php

namespace App\Http\V_1_0_0\Migration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor_categories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_en', 'name_ar', 'slug', 'active', 'created_by', 'updated_by'
    ];

    /* To check the category name is already exist or not */
    public static function getCategoryDetail($name_slug)
    {
        return Vendor_categories::select('id', 'name_en', 'name_ar')->where('slug','=',Str::slug($name_slug))->first();
    }
    /* To get the vendor categories using akeed service id */
    public static function getCategoryDetailById($akeed_service_id)
    {
        return Vendor_categories::select('id', 'name_en', 'name_ar')->where('akeed_service_id','=',$akeed_service_id)->first();
    }
    public static function InsertVendorCategory($categoryName, $akeed_service_id)
    {
        $service_categories = new Vendor_categories;
        $service_categories->name_en = $categoryName;
        $service_categories->akeed_service_id = $akeed_service_id;
        $service_categories->slug = Str::slug($categoryName);
        $service_categories->active = '1';
        $service_categories->save();
        return $service_categories->first();
    }
}
