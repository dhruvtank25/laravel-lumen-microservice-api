<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;

use App\Http\V_1_0_0\Orders\Models\Orders;
use App\Http\V_1_0_0\Notifications\Models\Notifications;
use App\Http\V_1_0_0\Orders\Models\Drivers;
use App\Http\V_1_0_0\Orders\Models\Authentication_devices;
use App\Helpers\ResponseBuilder;

//use Cache;

class readyforDispatchController extends Controller
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
    public function readyforDispatch(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['reason'] = isset($input['reason']) ? trim($input['reason']) : '';
            $input['language'] = (isset($input['language']) && $input['language'] == 'ar') ? 'ar' : 'en';
            $rules = [
                //'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'order_id' => ['required', 'numeric', 'exists:orders,id'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'reason' => ['regex:/^[a-zA-Z0-9 ._-]+$/']
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
            $vendorAuthenticationId = $credentials->sub;
            /* To get the order details */
            $orderDetail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
            if ($orderDetail) {
                if ($vendorAuthenticationId <> $orderDetail->vendor_authentication_id) {
                    return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Token.');
                }
                if($orderDetail->STATUSID == 6){
                    return ResponseBuilder::responseResult($this->failureStatus, 'This order is already delivered.');
                }
                if($orderDetail->STATUSID == 7){
                    return ResponseBuilder::responseResult($this->failureStatus, 'This order is already rejected.');
                }
                if (empty($orderDetail->ready_for_pickup_time)) {
                    /*if (!empty($orderDetail->akeed_order_id)) {
                        $client = new \GuzzleHttp\Client();
                        $response = $client->request('POST', env('DOT_NET_BASE_URL') . '/Monolith/VendorOrderStatusChanges', ['form_params' => [
                            'OrderId' => $orderDetail->akeed_order_id,
                            'Status' => 4,//4-Ready for dispatch
                            'Reason' => $input['reason']
                        ]]);
                        $contents = $response->getBody()->getContents();
                        //if ($contents <> 'record updated successfully') {
                            //return ResponseBuilder::responseResult($this->failureStatus, 'Order Status not changed, because of monolithic issue.');
                        //}
                    }*/
                    Orders::ReadyforDispatch($input['order_id'], $input['reason'], $vendorAuthenticationId);
                    if (!empty($orderDetail->driver_id)) {
                        /* To get the customer authentication details */
                        $deviceDetail = Authentication_devices::getAuthenticationDeviceDetails($orderDetail->driver_authentication_id);
                        if (!empty($deviceDetail) && isset($deviceDetail->fcm_token) && !empty($deviceDetail->fcm_token)) {
                            $delivery_date = $orderDetail->delivery_time;
                            if ($orderDetail->delivery_time_type == 'as_soon_as') {
                                $delivery_date = $orderDetail->formated_created_at.' / As soon as possible';
                            }
                            /* To send the push notification to the driver */
                            $driver_notitification_title = 'Ready For Pick Up';
                            $driver_notitification_message = "Order Id " . $orderDetail->akeed_order_id . " is Ready For Pickup from " . trim($orderDetail->VendorName);
                            $vendorNameAr = !empty(trim($orderDetail->VendorNameAr))?trim($orderDetail->VendorNameAr):trim($orderDetail->VendorName);
                            //if ($input['language'] == 'ar') {
                                $driver_notitification_title_ar = 'جاهز للإستلام';
                                $driver_notitification_message_ar = "الطلب " . $orderDetail->akeed_order_id . " جاهز للاستلام من ".$vendorNameAr;
                            //}
                            $receiver_role = 3;
                            $dataInput = ['sender_id' => $vendorAuthenticationId, 'sender_role' => 2, 'receiver_id' => $orderDetail->driver_authentication_id, 'receiver_role' => $receiver_role, 'created_by' => $vendorAuthenticationId];
                            $data_array = ['title' => $driver_notitification_title, 'title_ar' => $driver_notitification_title_ar, 'message' => $driver_notitification_message, 'message_ar' => $driver_notitification_message_ar, 'notification_type' => 2, 'order_id' => $input['order_id'], 'delivery' => $delivery_date, 'location' => '', 'instruction' => $orderDetail->DRIVERINSTRUCTION, 'Force' => 0];//'notification_type' => 3,-As per android developer request. we have changed to notification_type - w
                            /* To send the push notification */
                            Notifications::Send_notification($deviceDetail->device_type, $deviceDetail->fcm_token, $data_array, $dataInput);
                        }
                    }
                }
                return ResponseBuilder::responseResult($this->successStatus, 'record updated successfully');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Order');

        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function readyforDispatchlist(Request $request)
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
                $new_order_list = Orders::ReadyforDispatchlist($input['vendor_id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Order List has been fetched successfully', $new_order_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

}
