<?php

namespace App\Http\V_1_0_0\Profile\Models;

use Illuminate\Database\Eloquent\Model;


class Vendors extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authentication_id', 'firstname', 'lastname', 'alternate_mobile', 'alternate_mobile_country_code', 'profile_image', 'gender', 'dob', 'status', 'verified', 'language'
    ];

    public static function Update_vendor($input)
    {
        $vendors = Vendors::where('id', "=", $input['vendor_id'])->first();

        $vendors->shop_name_en = $input['shop_name_en'];
        $vendors->shop_name_ar = $input['shop_name_ar'];
        if (!empty($input['AlternateMobile'])) {
            $vendors->alternate_mobile = $input['AlternateMobile'];
        } else {
            $vendors->alternate_mobile = NULL;
        }

        $vendors->address_1 = $input['Address1'];
        $vendors->address_2 = $input['Address2'];
        $vendors->landmark = $input['Landmark'];
        $vendors->latitude = $input['Latitude'];
        $vendors->longitude = $input['Longitude'];
        $vendors->tag_line_en = $input['Tagline'];
        $vendors->tag_line_ar = $input['ARTagline'];

        if (isset($input['logo'])) {
            $vendors->logo = $input['logo'];
        }

        $vendors->save();
        return $vendors;
    }

    public static function getAuthenticationDetails($vendor_id)
    {
        $vendor = Vendors::select('authentication_id')->where('id', $vendor_id);
        return $vendor->first();
    }

    public static function getVendorList($vendor_id)
    {
        $vendor = Vendors::select('vendors.id as vendor_id', 'vendors.firstname', 'vendors.firstname_ar', 'vendors.lastname', 'vendors.lastname_ar', 'vendors.shop_name_en', 'vendors.shop_name_ar', 'a.phone', 'alternate_mobile', 'a.email', 'address_1', 'address_2', 'landmark', 'prepration_time', 'vendor_category_id', 'vendor_category_en', 'vendor_category_ar', 'latitude', 'longitude', 'logo', 'tag_line_en', 'tag_line_ar', 'delivery_charge', 'municipal_tax', 'service_charge', 'is_akeed_delivering', 'min_order_discount', 'discount_amount', 'discount_percentage', 'discount_active_from', 'discount_active_to', 'akeed_percentage', 'serving_distance', 'm_m.name_en as menu_master_name_en', 'm_m.name_ar as menu_master_name_ar', 'm_m.description_en as menu_master_description_en', 'm_m.description_ar as menu_master_description_ar', 'rank', 'discount_type', 'akeed_vendor_id')
            ->join('authentications as a', 'a.id', '=', 'vendors.authentication_id')
            ->leftJoin('menu_masters as m_m', 'm_m.vendor_id', '=', 'vendors.id')
            ->where('vendors.id', '=', $vendor_id);
        return $vendor->get();

    }

    /* To get the vendor open status details */
    public static function getVendorOpenStatusDetail($authentication_id)
    {
        return Vendors::select('id', 'is_open')->where('authentication_id', $authentication_id)->first();
    }

    /* To update the vendor open status */
    public static function updateVendorOpenStatus($input)
    {
        $vendors = Vendors::find($input['vendor_id']);
        $vendors->is_open = $input['is_open'];
        $vendors->updated_by = $input['authentication_id'];
        $vendors->save();
        return true;
    }
}
