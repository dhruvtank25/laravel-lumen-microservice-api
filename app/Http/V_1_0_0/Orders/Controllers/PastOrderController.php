<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\Orders\Models\Orders;
use App\Http\V_1_0_0\Orders\Models\Vendors;
use App\Helpers\ResponseBuilder;

//use Cache;

class PastOrderController extends Controller
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
    public function pastOrderList(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id']
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
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $vendorAuthenticationId = $credentials->sub;
                /* To get the authentication detail */
                $vendorDetail = Vendors::getAuthenticationDetails($input['vendor_id']);
                if ($vendorDetail->authentication_id <> $vendorAuthenticationId) {
                    return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Vendor');
                }
                $past_order_list = Orders::getPastOrderList($input['vendor_id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Past Order List has been fetched successfully',$past_order_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch(\Firebase\JWT\ExpiredException $e){
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
