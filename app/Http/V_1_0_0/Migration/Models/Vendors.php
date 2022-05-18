<?php

namespace App\Http\V_1_0_0\Migration\Models;

use Illuminate\Database\Eloquent\Model;


class Vendors extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authentication_id', 'akeed_vendor_id', 'shop_name_en', 'shop_name_ar', 'tag_line_en', 'tag_line_ar', 'email', 'phone', 'alternate_mobile_country_code', 'alternate_mobile', 'address_1', 'address_2', 'landmark', 'latitude', 'longitude', 'logo', 'web_logo', 'gender', 'dob', 'salary', 'vendor_category_id', 'vendor_category_en', 'vendor_category_ar', 'OpeningTime', 'OpeningTime2', 'commission', 'delivery_charge', 'service_charge', 'municipal_tax', 'is_akeed_delivering', 'is_open', 'prepration_time', 'akeed_percentage', 'discount_active_form', 'discount_active_to', 'min_order_discount', 'discount_amount', 'discount_percentage', 'language', 'status', 'verified', 'created_at', 'updated_at'
    ];
    /* To check email id already exist or not */
    public static function checkEmailAlreadyExistOrNot($email)
    {
        return Vendors::select('id')->where('email','=',$email)->first();
    }
    /* To check mobile number already exist or not */
    public static function checkMobileNoAlreadyExistOrNot($phone)
    {
        return Vendors::select('id')->where('phone','=',$phone)->first();
    }

    public static function Insert_vendor($input)
    {
        $vendors = new Vendors;
        $vendors->authentication_id = $input['authentication_id'];
        $vendors->akeed_vendor_id = $input['akeed_user_id'];
        $vendors->shop_name_en = $input['shop_name_en'];
        $vendors->shop_name_ar = $input['shop_name_ar'];
        $vendors->tag_line_en = $input['tag_line_en'];
        $vendors->tag_line_ar = $input['tag_line_ar'];
        $vendors->email = $input['email'];
        $vendors->phone = $input['phone'];
        $vendors->alternate_mobile_country_code = $input['alternate_mobile_country_code'];
        $vendors->alternate_mobile = $input['alternate_mobile'];
        $vendors->address_1 = $input['address_1'];
        $vendors->address_2 = $input['address_2'];
        $vendors->landmark = $input['landmark'];
        $vendors->latitude = $input['latitude'];
        $vendors->longitude = $input['longitude'];
        $vendors->logo = $input['logo'];
        $vendors->web_logo = $input['web_logo'];
        $vendors->gender = $input['gender'];
        //$vendors->dob = $input['dob'];
        $vendors->salary = $input['salary'];
        $vendors->vendor_category_id = $input['vendor_category_id'];
        $vendors->vendor_category_en = $input['vendor_category_en'];
        $vendors->vendor_category_ar = $input['vendor_category_ar'];
        $vendors->OpeningTime = $input['OpeningTime'];
        $vendors->OpeningTime2 = $input['OpeningTime2'];
        $vendors->commission = $input['commission'];
        $vendors->delivery_charge = $input['delivery_charge'];
        $vendors->service_charge = $input['service_charge'];
        $vendors->municipal_tax = $input['municipal_tax'];
        $vendors->is_akeed_delivering = $input['is_akeed_delivering'];
        $vendors->is_open = $input['is_open'];
        $vendors->serving_distance = $input['serving_distance'];
        $vendors->prepration_time = $input['prepration_time'];
        $vendors->akeed_percentage = $input['akeed_percentage'];
        $vendors->discount_active_from = $input['discount_active_from'];
        $vendors->discount_active_to = $input['discount_active_to'];
        $vendors->min_order_discount = $input['min_order_discount'];
        $discountType = '';
        if ($input['discount_amount'] > 0) {
            $discountType = 'amount';
        } elseif ($input['discount_percentage'] > 0) {
            $discountType = 'percentage';
        }
        $vendors->discount_type = $discountType;
        $vendors->discount_amount = $input['discount_amount'];
        $vendors->discount_percentage = $input['discount_percentage'];
        $vendors->language = $input['language'];
        $vendors->status = $input['status'];
        $vendors->open_close_flags = $input['open_close_flags'];
        $vendors->verified = $input['verified'];
        $vendors->created_at = $input['created_at'];
        $vendors->updated_at = $input['updated_at'];
        $vendors->save();
        return $vendors->id;
    }
    /* To get the vendor details */
    public static function getVendorDetail($akeed_vendor_id)
    {
        $vendorDetail = Vendors::select('id')->where('akeed_vendor_id',$akeed_vendor_id)->first();
        return $vendorDetail;
    }
    /* To update the vendor */
    public static function Update_vendor($input, $id)
    {
        $vendors = Vendors::find($id);
        if (!empty($vendors)) {
            $vendors->authentication_id = $input['authentication_id'];
            $vendors->akeed_vendor_id = $input['akeed_user_id'];
            $vendors->shop_name_en = $input['shop_name_en'];
            $vendors->shop_name_ar = $input['shop_name_ar'];
            $vendors->tag_line_en = $input['tag_line_en'];
            $vendors->tag_line_ar = $input['tag_line_ar'];
            $vendors->email = $input['email'];
            $vendors->phone = $input['phone'];
            $vendors->alternate_mobile_country_code = $input['alternate_mobile_country_code'];
            $vendors->alternate_mobile = $input['alternate_mobile'];
            $vendors->address_1 = $input['address_1'];
            $vendors->address_2 = $input['address_2'];
            $vendors->landmark = $input['landmark'];
            $vendors->latitude = $input['latitude'];
            $vendors->longitude = $input['longitude'];
            $vendors->logo = $input['logo'];
            $vendors->web_logo = $input['web_logo'];
            $vendors->gender = $input['gender'];
            //$vendors->dob = $input['dob'];
            $vendors->salary = $input['salary'];
            $vendors->vendor_category_id = $input['vendor_category_id'];
            $vendors->vendor_category_en = $input['vendor_category_en'];
            $vendors->vendor_category_ar = $input['vendor_category_ar'];
            $vendors->OpeningTime = $input['OpeningTime'];
            $vendors->OpeningTime2 = $input['OpeningTime2'];
            $vendors->commission = $input['commission'];
            $vendors->delivery_charge = $input['delivery_charge'];
            $vendors->service_charge = $input['service_charge'];
            $vendors->municipal_tax = $input['municipal_tax'];
            $vendors->is_akeed_delivering = $input['is_akeed_delivering'];
            $vendors->is_open = $input['is_open'];
            $vendors->serving_distance = $input['serving_distance'];
            $vendors->prepration_time = $input['prepration_time'];
            $vendors->akeed_percentage = $input['akeed_percentage'];
            $vendors->discount_active_from = $input['discount_active_from'];
            $vendors->discount_active_to = $input['discount_active_to'];
            $vendors->min_order_discount = $input['min_order_discount'];
            $discountType = '';
            if ($input['discount_amount'] > 0) {
                $discountType = 'amount';
            } elseif ($input['discount_percentage'] > 0) {
                $discountType = 'percentage';
            }
            $vendors->discount_type = $discountType;
            $vendors->discount_amount = $input['discount_amount'];
            $vendors->discount_percentage = $input['discount_percentage'];
            $vendors->language = $input['language'];
            $vendors->status = $input['status'];
            $vendors->open_close_flags = $input['open_close_flags'];
            $vendors->verified = $input['verified'];
            $vendors->created_at = $input['created_at'];
            $vendors->updated_at = $input['updated_at'];
            $vendors->save();
            return $vendors->id;
        }
        return true;
    }
}
