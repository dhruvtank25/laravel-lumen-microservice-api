<?php

namespace App\Http\V_1_0_0\Profile\Controllers;

use App\Helpers\ResponseBuilder;
use App\Http\Controller;
use App\Http\V_1_0_0\Profile\Models\Authentications;
use App\Http\V_1_0_0\Profile\Models\Vendor_category;
use App\Http\V_1_0_0\Profile\Models\Vendors;
use App\Http\V_1_0_0\Profile\Models\Vendor_primary_tag_mappings;
use App\Http\V_1_0_0\ZoneCacheRefresh\Models\ZoneCacheRefresh;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Validator;

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

    public function VendorDetails(Request $request)
    {
        try {
            $input = $request->all();
            $vendor_id = $input['vendor_id'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $vendor_list = Vendors::getVendorList($vendor_id);
                return ResponseBuilder::responseResult($this->successStatus, 'Vendor Details has been fetched successfully', $vendor_list);

            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function UpdateProfile(Request $request)
    {
        try {
            $input = $request->all();
//return $input;
            $input['shop_name_en'] = isset($input['shop_name_en']) ? trim($input['shop_name_en']) : '';
            $input['shop_name_ar'] = isset($input['shop_name_ar']) ? trim($input['shop_name_ar']) : '';
            $input['Address1'] = isset($input['Address1']) ? trim($input['Address1']) : '';
            $input['Address2'] = isset($input['Address2']) ? trim($input['Address2']) : '';
            $input['Landmark'] = isset($input['Landmark']) ? trim($input['Landmark']) : '';
            $input['Latitude'] = isset($input['Latitude']) ? trim($input['Latitude']) : 0;
            $input['Longitude'] = isset($input['Longitude']) ? trim($input['Longitude']) : 0;
            $input['Tagline'] = isset($input['Tagline']) ? trim($input['Tagline']) : '';
            $input['ARTagline'] = isset($input['ARTagline']) ? trim($input['ARTagline']) : '';

            $rules = [
                'vendor_id' => ['required'],
                'shop_name_en' => ['required', 'max:32', 'regex:/^[A-Za-z0-9_.,!@#$%^*&()\-? ]+$/'],
                'shop_name_ar' => ['max:32'],
                'Address1' => ['required'],
                'Latitude' => ['required', 'between:0,99.99999999'],
                'Longitude' => ['required', 'between:0,180.99999999']
            ];

            if (isset($input['AlternateMobile'])) {
                $rules['AlternateMobile'] = 'numeric|digits:8';
            }
            if ($input['Landmark'] != '') {
                $rules['Landmark'] = 'max:32|regex:/^[a-zA-Z0-9 \@\#\$\%\*\(\)\&\.\,\!\-\_\']+$/';
            }
            if ($input['Tagline'] != '') {
                $rules['Tagline'] = 'max:150|regex:/^[a-zA-Z0-9 &()!@#%$*.\,]+$/';
            }
            if ($input['ARTagline'] != '') {
                $rules['ARTagline'] = 'max:150';
            }

            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }

            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $authentication_id = Vendors::getAuthenticationDetails($input['vendor_id']);
                $input['authentication_id'] = $authentication_id->authentication_id;
                Authentications::update_authentication($input);
                $vendor = Vendors::Update_vendor($input);
                ZoneCacheRefresh::refreshCache($input['vendor_id']);

                return ResponseBuilder::responseResult($this->successStatus, 'Vendor updated successfully', $vendor);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function RestuarantCategory(Request $request)
    {
        try {
            $input = $request->all();

            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $category = Vendor_category::getVendorCategory();

                return ResponseBuilder::responseResult($this->successStatus, 'category list', $category);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To get the open status detail */
    public function openStatusDetail(Request $request)
    {
        try {
            $input = $request->all();
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $vendorOpenStatus = Vendors::getVendorOpenStatusDetail($input['authentication_id']);
                $data['is_open'] = isset($vendorOpenStatus) ? $vendorOpenStatus->is_open : '0';
                return ResponseBuilder::responseResult($this->successStatus, 'Vendor open status detail', $data);
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the vendor status */
    public function updateOpenStatus(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'vendor_id' => ['required', 'exists:vendors,id'],
                'is_open' => ['required', 'boolean'],
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
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                Vendors::updateVendorOpenStatus($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Vendor open status updated successfully');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To get the vendor primary tag list */
    public function vendorPrimaryTagList(Request $request)
    {
        try {
            $input = $request->all();
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';

            $rules = [
                'vendor_id' => ['required', 'exists:vendors,id']
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

            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $vendorPrimaryTags = Vendor_primary_tag_mappings::getVendorPrimaryTagMastersList($input['vendor_id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Vendor - Primary Tag List', $vendorPrimaryTags);
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}

