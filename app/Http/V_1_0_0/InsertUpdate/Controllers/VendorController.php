<?php

namespace App\Http\V_1_0_0\InsertUpdate\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use App\Http\V_1_0_0\InsertUpdate\Models\Vendors;
use App\Http\V_1_0_0\InsertUpdate\Models\Authentications;
use App\Http\V_1_0_0\InsertUpdate\Models\Menu_masters;
use App\Http\V_1_0_0\InsertUpdate\Models\Vendor_categories;
use App\Http\V_1_0_0\ZoneCacheRefresh\Models\ZoneCacheRefresh;

use App\Helpers\ResponseBuilder;

//use Cache;

class VendorController extends Controller
{
    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function addVendor(Request $request)
    {
        try {
            $input = $request->all();
            $input['EmailId'] = isset($input['EmailId']) ? trim($input['EmailId']) : '';
            $input['MobileNo'] = isset($input['MobileNo']) ? trim($input['MobileNo']) : '';
            $input['Password'] = isset($input['Password']) ? trim($input['Password']) : '';
            $input['FirstName'] = isset($input['FirstName']) ? trim($input['FirstName']) : '';
            $input['LastName'] = isset($input['LastName']) ? trim($input['LastName']) : '';
            $input['AlternateMobile'] = trim($input['AlternateMobile']);
            $input['Address1'] = isset($input['Address1']) ? trim($input['Address1']) : '';
            $input['Address2'] = isset($input['Address2']) ? trim($input['Address2']) : '';
            $input['Landmark'] = isset($input['Landmark']) ? trim($input['Landmark']) : '';
            $input['path'] = isset($input['path']) ? trim($input['path']) : '';
            $input['otp'] = isset($input['otp']) ? trim($input['otp']) : '';
            $input['Time'] = isset($input['Time']) ? trim($input['Time']) : '';
            $input['ETime'] = isset($input['ETime']) ? trim($input['ETime']) : '';
            $input['ResturantsCategory'] = isset($input['ResturantsCategory']) ? trim($input['ResturantsCategory']) : '';
            $input['Lat'] = isset($input['Lat']) ? trim($input['Lat']) : 0;
            $input['Lon'] = isset($input['Lon']) ? trim($input['Lon']) : 0;
            $input['Tagline'] = isset($input['Tagline']) ? trim($input['Tagline']) : '';
            $input['DeliveryCharge'] = (isset($input['DeliveryCharge']) && is_numeric($input['DeliveryCharge'])) ? trim($input['DeliveryCharge']) : 0;
            $input['MunicipalityTax'] = (isset($input['MunicipalityTax']) && is_numeric($input['MunicipalityTax'])) ? trim($input['MunicipalityTax']) : 0;
            $input['ServiceChrge'] = (isset($input['ServiceChrge']) && is_numeric($input['ServiceChrge'])) ? trim($input['ServiceChrge']) : 0;
            $input['IsAkeedDelivering'] = isset($input['IsAkeedDelivering']) ? trim($input['IsAkeedDelivering']) : '';
            $input['ARFirstName'] = isset($input['ARFirstName']) ? trim($input['ARFirstName']) : '';
            $input['ARLastName'] = isset($input['ARLastName']) ? trim($input['ARLastName']) : '';
            $input['ARTagline'] = isset($input['ARTagline']) ? trim($input['ARTagline']) : '';
            $input['PreparationTime'] = (isset($input['PreparationTime']) && is_numeric($input['PreparationTime'])) ? trim($input['PreparationTime']) : 0;
            $input['AkeedPercentage'] = (isset($input['AkeedPercentage']) && is_numeric($input['AkeedPercentage'])) ? trim($input['AkeedPercentage']) : 0;
            $input['MinOrderForDiscount'] = (isset($input['MinOrderForDiscount']) && is_numeric($input['MinOrderForDiscount'])) ? trim($input['MinOrderForDiscount']) : 0;
            $input['DiscountAmount'] = (isset($input['DiscountAmount']) && is_numeric($input['DiscountAmount'])) ? trim($input['DiscountAmount']) : 0;
            $input['DiscountPercentage'] = (isset($input['DiscountPercentage']) && is_numeric($input['DiscountPercentage'])) ? trim($input['DiscountPercentage']) : 0;
            $input['akeedid'] = isset($input['akeedid']) ? trim($input['akeedid']) : 0;
            $input['IsVerified'] = isset($input['IsVerified']) ? trim($input['IsVerified']) : 0;
            $input['serving_distance'] = (isset($input['Servingdistance']) && is_numeric($input['Servingdistance'])) ? trim($input['Servingdistance']) : 0;
            if (!empty(trim($input['DiscountActiveFrom'])) && !empty(trim($input['DiscountActiveTo']))) {
                $from = str_replace('-', '/', trim($input['DiscountActiveFrom']));
                $to = str_replace('-', '/', trim($input['DiscountActiveTo']));
                $input['DiscountActiveFrom'] = date('Y-m-d', strtotime($from));
                $input['DiscountActiveTo'] = date('Y-m-d', strtotime($to));
            } else {
                $input['DiscountActiveFrom'] = $input['DiscountActiveTo'] = '';
            }

            $rules = [
                'FirstName' => ['required', 'max:100'],
                'MobileNo' => ['required', 'unique:authentications,phone'],
                'EmailId' => ['required', 'email', 'max:150', 'unique:authentications,email'],
                'Password' => ['required'],
                'path' => ['required'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $input['status'] = $input['is_open'] = $input['open_close_flags'] = '0';
            /* To get the last open_close_flags */
            $lastVendor = Vendors::lastVendorDetail();
            if (isset($lastVendor->open_close_flags) && !empty($lastVendor->open_close_flags)) {
                $input['open_close_flags'] = $lastVendor->open_close_flags;
            }

            $authentication_id = Authentications::Insert_Authentication($input);

            $input['authentication_id'] = $authentication_id;
            $input['ResturantsCategoryId'] = 0;
            $input['ResturantsCategoryAr'] = '';
            /* to get the Vendor Category detail */
            $categoryDetail = Vendor_categories::getVendorCategoryDetails($input['ResturantsCategory']);
            if (!empty($categoryDetail)) {
                $input['ResturantsCategoryId'] = $categoryDetail->id;
                $input['ResturantsCategoryAr'] = $categoryDetail->name_ar;
            }

            $vendor_id = Vendors::Insert_vendor($input);

            $data['authentication_id'] = $authentication_id;
            $data['vendor_id'] = $vendor_id;
            $input['vendor_id'] = $vendor_id;
            Menu_masters::Insert_menu($input);
            ZoneCacheRefresh::refreshCache($vendor_id);
            return ResponseBuilder::responseResult($this->successStatus, 'Vendor Saved Successfully', $data);

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function UpdateVendor(Request $request)
    {
        try {
            $input = $request->all();
            $input['EmailId'] = isset($input['EmailId']) ? trim($input['EmailId']) : '';
            $input['MobileNo'] = isset($input['MobileNo']) ? trim($input['MobileNo']) : '';
            $input['Password'] = isset($input['Password']) ? trim($input['Password']) : '';
            $input['FirstName'] = isset($input['FirstName']) ? $input['FirstName'] : '';
            $input['LastName'] = isset($input['LastName']) ? trim($input['LastName']) : '';
            $input['AlternateMobile'] = isset($input['ALTERNATEMOBILE']) ? trim($input['ALTERNATEMOBILE']) : '';
            $input['Address1'] = isset($input['Address1']) ? trim($input['Address1']) : '';
            $input['Address2'] = isset($input['Address2']) ? trim($input['Address2']) : '';
            $input['Landmark'] = isset($input['Landmark']) ? trim($input['Landmark']) : '';
            $input['Time'] = isset($input['Time']) ? trim($input['Time']) : '';
            $input['ETime'] = isset($input['ETime']) ? trim($input['ETime']) : '';
            $input['ResturantsCategory'] = isset($input['ResturantsCategory']) ? trim($input['ResturantsCategory']) : '';
            $input['Latitude'] = isset($input['Latitude']) ? trim($input['Latitude']) : 0;
            $input['Longitude'] = isset($input['Longitude']) ? trim($input['Longitude']) : 0;
            $input['Tagline'] = isset($input['Tagline']) ? trim($input['Tagline']) : '';
            $input['DeliveryCharge'] = (isset($input['DeliveryCharge']) && is_numeric($input['DeliveryCharge'])) ? trim($input['DeliveryCharge']) : 0;
            $input['MunicipalityTax'] = (isset($input['MunicipalityTax']) && is_numeric($input['MunicipalityTax'])) ? trim($input['MunicipalityTax']) : 0;
            $input['ServiceChrge'] = (isset($input['ServiceChrge']) && is_numeric($input['ServiceChrge'])) ? trim($input['ServiceChrge']) : 0;
            $input['IsAkeedDelivering'] = isset($input['IsAkeedDelivering']) ? trim($input['IsAkeedDelivering']) : '';
            $input['ARFirstName'] = isset($input['ARFirstName']) ? trim($input['ARFirstName']) : '';
            $input['ARLastName'] = isset($input['ARLastName']) ? trim($input['ARLastName']) : '';
            $input['ARTagline'] = isset($input['ARTagline']) ? trim($input['ARTagline']) : '';
            $input['PreparationTime'] = (isset($input['PreparationTime']) && is_numeric($input['PreparationTime'])) ? trim($input['PreparationTime']) : 0;
            $input['akeedID'] = isset($input['akeedID']) ? trim($input['akeedID']) : '';
            $input['AkeedPercentage'] = (isset($input['AkeedPercentage']) && is_numeric($input['AkeedPercentage'])) ? trim($input['AkeedPercentage']) : 0;
            $input['MinOrderForDiscount'] = (isset($input['MinOrderForDiscount']) && is_numeric($input['MinOrderForDiscount'])) ? trim($input['MinOrderForDiscount']) : 0;
            $input['DiscountAmount'] = (isset($input['DiscountAmount']) && is_numeric($input['DiscountAmount'])) ? trim($input['DiscountAmount']) : 0;
            $input['DiscountPercentage'] = (isset($input['DiscountPercentage']) && is_numeric($input['DiscountPercentage'])) ? trim($input['DiscountPercentage']) : 0;
            $input['serving_distance'] = (isset($input['Servingdistance']) && is_numeric($input['Servingdistance'])) ? trim($input['Servingdistance']) : 0;
            $input['path'] = isset($input['path']) ? trim($input['path']) : '';
            if (!empty(trim($input['DiscountActiveFrom'])) && !empty(trim($input['DiscountActiveTo']))) {
                $from = str_replace('-', '/', trim($input['DiscountActiveFrom']));
                $to = str_replace('-', '/', trim($input['DiscountActiveTo']));
                $input['DiscountActiveFrom'] = date('Y-m-d', strtotime($from));
                $input['DiscountActiveTo'] = date('Y-m-d', strtotime($to));
            } else {
                $input['DiscountActiveFrom'] = $input['DiscountActiveTo'] = '';
            }

            $rules = [
                'FirstName' => ['required', 'max:100'],
                'LastName' => ['max:100'],
                'MobileNo' => ['required'],
                'EmailId' => ['required', 'email', 'max:150'],
                'Password' => ['required'],

            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            Authentications::update_authentication($input);
            $input['ResturantsCategoryId'] = 0;
            $input['ResturantsCategoryAr'] = '';
            /* to get the Vendor Category detail */
            $categoryDetail = Vendor_categories::getVendorCategoryDetails($input['ResturantsCategory']);
            if (!empty($categoryDetail)) {
                $input['ResturantsCategoryId'] = $categoryDetail->id;
                $input['ResturantsCategoryAr'] = $categoryDetail->name_ar;
            }
            $update_vendor = Vendors::Update_vendor($input);
            $input['vendor_id'] = isset($update_vendor->id) ? $update_vendor->id : 0;
            //Menu_masters::Update_menu($input);

            ZoneCacheRefresh::refreshCache($input['vendor_id']);
            return ResponseBuilder::responseResult($this->successStatus, 'Vendor updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function refresh_cache(Request $request)
    {
        $input = $request->all();
        $vendor_id = $input['vendor_id'];
        ZoneCacheRefresh::refreshCache($vendor_id);
        echo "asd";
        exit;
    }

    /* To update the vendor timing */
    public function updateVendorTiming(Request $request)
    {
        try {
            $input = $request->all();
            $input['vendorid'] = isset($input['vendorid']) ? trim($input['vendorid']) : '';
            $input['time1'] = isset($input['time1']) ? trim($input['time1']) : '';
            $input['time2'] = isset($input['time2']) ? trim($input['time2']) : '';
            $input['time3'] = isset($input['time3']) ? trim($input['time3']) : '';
            $input['time4'] = isset($input['time4']) ? trim($input['time4']) : '';
            $input['day'] = isset($input['day']) ? trim($input['day']) : '';

            $vendor_id = Vendors::UpdateVendorTiming($input);

            ZoneCacheRefresh::refreshCache($vendor_id);
            return ResponseBuilder::responseResult($this->successStatus, 'Vendor Timing Saved Successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the vendor status */
    public function updateVendorStatus(Request $request)
    {
        try {
            $input = $request->all();
            $input['type'] = isset($input['type']) ? trim($input['type']) : 'status';
            $input['value'] = isset($input['value']) ? trim($input['value']) : '0';
            $input['vendorid'] = isset($input['vendorid']) ? trim($input['vendorid']) : 0;

            $vendor_id = Vendors::UpdateVendorStatus($input);

            ZoneCacheRefresh::refreshCache($vendor_id);
            return ResponseBuilder::responseResult($this->successStatus, 'Vendor Status Changed Successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
