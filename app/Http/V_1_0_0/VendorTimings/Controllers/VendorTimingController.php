<?php

namespace App\Http\V_1_0_0\VendorTimings\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;

use Firebase\JWT\JWT;
use App\Http\V_1_0_0\VendorTimings\Models\Vendors;
use App\Helpers\ResponseBuilder;

//use Cache;

class VendorTimingController extends Controller
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
    public function TimingList(Request $request)
    {

        try {
            $input = $request->all();
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $timing_list = Vendors::getTimingList($input['vendor_id']);

                return ResponseBuilder::responseResult($this->successStatus, 'Order List',$timing_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function UpdateTimings(Request $request)
    {
        try {
            $input = $request->all();
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';

            $rules = [
                'vendor_id' => ['required', 'max:32'],
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
                Vendors::Update_timing($input);
                $vendor_list = Vendors::getTimingList($input['vendor_id']);
                //$data['akeed_vendor_id']=$vendor_list[0]->akeed_vendor_id;
                return ResponseBuilder::responseResult($this->successStatus, 'Time updated successfully');//, $data
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

}
