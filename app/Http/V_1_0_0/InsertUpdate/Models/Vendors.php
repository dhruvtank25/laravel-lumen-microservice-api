<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models;

use Illuminate\Database\Eloquent\Model;


class Vendors extends Model
{
    public static function Insert_vendor($input)
    {
        $vendors = new Vendors;
        $vendors->authentication_id = $input['authentication_id'];
        $vendors->shop_name_en =  $input['FirstName'].' '. $input['LastName'];
        $vendors->alternate_mobile = $input['AlternateMobile'];
        $vendors->address_1 = $input['Address1'];
        $vendors->address_2 = $input['Address2'];
        $vendors->email = $input['EmailId'];
        $vendors->phone = $input['MobileNo'];
        $vendors->landmark = $input['Landmark'];
        $vendors->logo = $input['path'];
        $vendors->OpeningTime = $input['Time'];
        $vendors->OpeningTime2 = $input['ETime'];
        $vendors->vendor_category_en = $input['ResturantsCategory'];
        $vendors->vendor_category_id = $input['ResturantsCategoryId'];
        $vendors->vendor_category_ar = $input['ResturantsCategoryAr'];
        $vendors->latitude = $input['Lat'];
        $vendors->longitude = $input['Lon'];
        $vendors->tag_line_en = $input['Tagline'];
        $vendors->delivery_charge = $input['DeliveryCharge'];
        $vendors->municipal_tax = $input['MunicipalityTax'];
        $vendors->service_charge = $input['ServiceChrge'];
        $vendors->is_akeed_delivering = $input['IsAkeedDelivering'];
        $vendors->shop_name_ar =$input['ARFirstName'].' '. $input['ARLastName'];
        $vendors->tag_line_ar = $input['ARTagline'];
        $vendors->prepration_time = $input['PreparationTime'];
        $vendors->status = $input['status'];
        $vendors->is_open = $input['is_open'];
        $vendors->open_close_flags = $input['open_close_flags'];
        $vendors->verified = $input['IsVerified'];
        $vendors->akeed_vendor_id = $input['akeedid'];
        $vendors->akeed_percentage = $input['AkeedPercentage'];
        $vendors->min_order_discount = $input['MinOrderForDiscount'];
        $vendors->discount_amount = $input['DiscountAmount'];
        $vendors->discount_percentage = $input['DiscountPercentage'];
        $vendors->discount_active_from = $input['DiscountActiveFrom'];
        $vendors->discount_active_to = $input['DiscountActiveTo'];
        $vendors->serving_distance = $input['serving_distance'];
        $vendors->save();
        return $vendors->id;
    }

    public static function Update_vendor($input)
    {
        $vendors = Vendors::where('akeed_vendor_id', "=", $input['akeedID'])->first();
        if (!empty($vendors)) {
            //  $vendors->authentication_id = $input['authentication_id'];
            $vendors->shop_name_en =  $input['FirstName'].' '. $input['LastName'];
            $vendors->alternate_mobile = $input['AlternateMobile'];
            $vendors->address_1 = $input['Address1'];
            $vendors->address_2 = $input['Address2'];
            $vendors->email = $input['EmailId'];
            $vendors->phone = $input['MobileNo'];
            $vendors->landmark = $input['Landmark'];
            $vendors->OpeningTime = $input['Time'];
            $vendors->OpeningTime2 = $input['ETime'];
            $vendors->vendor_category_en = $input['ResturantsCategory'];
            $vendors->vendor_category_id = $input['ResturantsCategoryId'];
            $vendors->vendor_category_ar = $input['ResturantsCategoryAr'];
            $vendors->latitude =  isset($input['Latitude'])?$input['Latitude']: $vendors->latitude;
            $vendors->longitude = $input['Longitude'];
            $vendors->tag_line_en = $input['Tagline'];
            $vendors->delivery_charge = $input['DeliveryCharge'];
            $vendors->municipal_tax = $input['MunicipalityTax'];
            $vendors->service_charge = $input['ServiceChrge'];
            $vendors->is_akeed_delivering = $input['IsAkeedDelivering'];
            $vendors->shop_name_ar =$input['ARFirstName'].' '. $input['ARLastName'];
            $vendors->tag_line_ar = $input['ARTagline'];
            $vendors->prepration_time = $input['PreparationTime'];
            $vendors->akeed_percentage = $input['AkeedPercentage'];
            $vendors->min_order_discount = $input['MinOrderForDiscount'];
            $vendors->discount_amount = $input['DiscountAmount'];
            $vendors->discount_percentage = $input['DiscountPercentage'];
            $vendors->discount_active_from = $input['DiscountActiveFrom'];
            $vendors->discount_active_to = $input['DiscountActiveTo'];
            $vendors->serving_distance = $input['serving_distance'];
            // $vendors->akeed_vendor_id = $input['akeedid'];
            if (!empty($input['path'])) {
                $vendors->logo = $input['path'];
            }
            $vendors->save();
            return $vendors;
        } else {
            return 0;
        }
    }
    /* To update the vendor timing */
    public static function UpdateVendorTiming($input)
    {
        $updateVendorTiming = Vendors::where('akeed_vendor_id', '=', $input['vendorid'])->first();
        if (!empty($updateVendorTiming)) {
            $from_time1 = $to_time1 = $from_time2 = $to_time2 = $from_time3 = $to_time3 = $from_time4 = $to_time4 = null;
            /* To check the first time is available or not */
            $time_1 = trim(trim($input['time1']),'-');
            if (!empty($time_1)) {
                $time1 = explode('-',str_replace('00:','12:',$time_1));
                if (!empty($time1)) {
                    $from_time1 = (isset($time1[0]) && !empty($time1[0]))?date('H:i:s',strtotime($time1[0])):null;
                    $to_time1 = (isset($time1[1]) && !empty($time1[1]))?date('H:i:s',strtotime($time1[1])):null;
                }
            }
            /* To check the second time is available or not */
            $time_2 = trim(trim($input['time2']),'-');
            if (!empty($time_2)) {
                $time2 = explode('-',str_replace('00:','12:',$time_2));
                if (!empty($time2)) {
                    $from_time2 = (isset($time2[0]) && !empty($time2[0]))?date('H:i:s',strtotime($time2[0])):null;
                    $to_time2 = (isset($time2[1]) && !empty($time2[1]))?date('H:i:s',strtotime($time2[1])):null;
                }
            }
            /* To check the third time is available or not */
            $time_3 = trim(trim($input['time3']),'-');
            if (!empty($time_3)) {
                $time3 = explode('-',str_replace('00:','12:',$time_3));
                if (!empty($time3)) {
                    $from_time3 = (isset($time3[0]) && !empty($time3[0]))?date('H:i:s',strtotime($time3[0])):null;
                    $to_time3 = (isset($time3[1]) && !empty($time3[1]))?date('H:i:s',strtotime($time3[1])):null;
                }
            }
            /* To check the fourth time is available or not */
            $time_4 = trim(trim($input['time4']),'-');
            if (!empty($time_4)) {
                $time4 = explode('-',str_replace('00:','12:',$time_4));
                if (!empty($time4)) {
                    $from_time4 = (isset($time4[0]) && !empty($time4[0]))?date('H:i:s',strtotime($time4[0])):null;
                    $to_time4 = (isset($time4[1]) && !empty($time4[1]))?date('H:i:s',strtotime($time4[1])):null;
                }
            }
            switch($input['day']){
                case 'Sun':
                    $updateVendorTiming->sunday_from_time1 =  $from_time1;
                    $updateVendorTiming->sunday_to_time1 = $to_time1;
                    $updateVendorTiming->sunday_from_time2 = $from_time2;
                    $updateVendorTiming->sunday_to_time2 = $to_time2;
                    $updateVendorTiming->sunday_from_time3 = $from_time3;
                    $updateVendorTiming->sunday_to_time3 = $to_time3;
                    $updateVendorTiming->sunday_from_time4 = $from_time4;
                    $updateVendorTiming->sunday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Mon':
                    $updateVendorTiming->monday_from_time1 =  $from_time1;
                    $updateVendorTiming->monday_to_time1 = $to_time1;
                    $updateVendorTiming->monday_from_time2 = $from_time2;
                    $updateVendorTiming->monday_to_time2 = $to_time2;
                    $updateVendorTiming->monday_from_time3 = $from_time3;
                    $updateVendorTiming->monday_to_time3 = $to_time3;
                    $updateVendorTiming->monday_from_time4 = $from_time4;
                    $updateVendorTiming->monday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Tue':
                    $updateVendorTiming->tuesday_from_time1 =  $from_time1;
                    $updateVendorTiming->tuesday_to_time1 = $to_time1;
                    $updateVendorTiming->tuesday_from_time2 = $from_time2;
                    $updateVendorTiming->tuesday_to_time2 = $to_time2;
                    $updateVendorTiming->tuesday_from_time3 = $from_time3;
                    $updateVendorTiming->tuesday_to_time3 = $to_time3;
                    $updateVendorTiming->tuesday_from_time4 = $from_time4;
                    $updateVendorTiming->tuesday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Wed':
                    $updateVendorTiming->wednesday_from_time1 =  $from_time1;
                    $updateVendorTiming->wednesday_to_time1 = $to_time1;
                    $updateVendorTiming->wednesday_from_time2 = $from_time2;
                    $updateVendorTiming->wednesday_to_time2 = $to_time2;
                    $updateVendorTiming->wednesday_from_time3 = $from_time3;
                    $updateVendorTiming->wednesday_to_time3 = $to_time3;
                    $updateVendorTiming->wednesday_from_time4 = $from_time4;
                    $updateVendorTiming->wednesday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Thu':
                    $updateVendorTiming->thursday_from_time1 =  $from_time1;
                    $updateVendorTiming->thursday_to_time1 = $to_time1;
                    $updateVendorTiming->thursday_from_time2 = $from_time2;
                    $updateVendorTiming->thursday_to_time2 = $to_time2;
                    $updateVendorTiming->thursday_from_time3 = $from_time3;
                    $updateVendorTiming->thursday_to_time3 = $to_time3;
                    $updateVendorTiming->thursday_from_time4 = $from_time4;
                    $updateVendorTiming->thursday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Fri':
                    $updateVendorTiming->friday_from_time1 =  $from_time1;
                    $updateVendorTiming->friday_to_time1 = $to_time1;
                    $updateVendorTiming->friday_from_time2 = $from_time2;
                    $updateVendorTiming->friday_to_time2 = $to_time2;
                    $updateVendorTiming->friday_from_time3 = $from_time3;
                    $updateVendorTiming->friday_to_time3 = $to_time3;
                    $updateVendorTiming->friday_from_time4 = $from_time4;
                    $updateVendorTiming->friday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
                case 'Sat':
                    $updateVendorTiming->saturday_from_time1 =  $from_time1;
                    $updateVendorTiming->saturday_to_time1 = $to_time1;
                    $updateVendorTiming->saturday_from_time2 = $from_time2;
                    $updateVendorTiming->saturday_to_time2 = $to_time2;
                    $updateVendorTiming->saturday_from_time3 = $from_time3;
                    $updateVendorTiming->saturday_to_time3 = $to_time3;
                    $updateVendorTiming->saturday_from_time4 = $from_time4;
                    $updateVendorTiming->saturday_to_time4 = $to_time4;
                    $updateVendorTiming->save();
                    break;
            }
            return $updateVendorTiming->id;
        }
        return 0;
    }
    /* To update the vendor status */
    public static function UpdateVendorStatus($input)
    {
        $updateVendorTiming = Vendors::where('akeed_vendor_id', '=', $input['vendorid'])->first();
        if (!empty($updateVendorTiming)) {
            if ($input['type'] == 'status') {
                $updateVendorTiming->status = $input['value'];
            } else {
                $updateVendorTiming->is_open = $input['value'];
            }
            $updateVendorTiming->save();
            return $updateVendorTiming->id;
        }
        return 0;
    }

    public static function getVendorDetails($authentication_id)
    {
        $vendor = Vendors::select('id')->where('authentication_id',  $authentication_id);
        return $vendor->first();
    }
    /* To get the last vendor details */
    public static function lastVendorDetail()
    {
        return Vendors::select('id','open_close_flags')->orderby('id','desc')->limit(1)->first();
    }

}
