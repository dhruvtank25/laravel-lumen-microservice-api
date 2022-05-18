<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\Orders\Models\Orders;
use App\Helpers\ResponseBuilder;
use App\Helpers\CommonFunction;

//use Cache;

class OrderDetailController extends Controller
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

    /*
     * order detail api
     *
     * @return \Illuminate\Http\Response
     */
    public function orderDetail(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'order_id' => ['required', 'numeric', 'exists:order_items,order_id']
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
                $order_detail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
                if ($order_detail) {
                    if (!empty($order_detail->translated_order_instructions)) {
                        $order_detail->INSTRUCTIONS = $order_detail->translated_order_instructions;
                    }
                    $order_detail->payment_mode_label = CommonFunction::getPaymentMode($order_detail->PAYMENTMODE);
                    $order_detail->MUNICIPALITYTAX = $order_detail->TAX;

                    if ($order_detail->ready_for_pickup_time != NULL && $order_detail->order_accepted_time != NULL) {
                        $from_time = strtotime($order_detail->ready_for_pickup_time);
                        $to_time = strtotime($order_detail->order_accepted_time);
                        $order_detail->preparation_time  = ceil(abs($to_time - $from_time) / 60);
                    } else {
                        $order_detail->preparation_time = "";
                    }
                    return ResponseBuilder::responseResult($this->successStatus, 'Order Details', $order_detail);
                }
                return ResponseBuilder::responseResult($this->failureStatus, 'Order not found');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
