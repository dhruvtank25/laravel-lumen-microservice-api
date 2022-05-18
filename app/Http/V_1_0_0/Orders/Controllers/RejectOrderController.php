<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;

use App\Http\V_1_0_0\Orders\Models\Orders;
use App\Http\V_1_0_0\Notifications\Models\Notifications;
use App\Http\V_1_0_0\Orders\Models\Authentication_devices;
use App\Http\V_1_0_0\GlobalSettings\Models\General_settings;
use App\Helpers\ResponseBuilder;

//use Cache;

class rejectOrderController extends Controller
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
    public function rejectOrder(Request $request)
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
                if ($orderDetail->STATUSID <> '7') {
                    if ($orderDetail->one_click_order == 'Y') {
                        $client = new \GuzzleHttp\Client();
                        $result = $client->post(env('ONE_CLICK_API') . '/customer/updateOrder', [
                            'form_params' => [
                                'hash' => env('ONE_CLICK_HASH'),
                                'status' => 4,
                            ]
                        ]);

                        $data = json_decode($result->getBody()->getContents());
                    }
                    /*if (!empty($orderDetail->akeed_order_id)) {
                        $client = new \GuzzleHttp\Client();
                        $response = $client->request('POST', env('DOT_NET_BASE_URL') . '/Monolith/VendorOrderStatusChanges', ['form_params' => [
                            'OrderId' => $orderDetail->akeed_order_id,
                            'Status' => 1002,//1002 - Reject order
                            'Reason' => $input['reason']
                        ]]);
                        $contents = $response->getBody()->getContents();
                        if ($contents <> 'record updated successfully') {
                            return ResponseBuilder::responseResult($this->failureStatus, 'Order Status not changed, because of monolithic issue.');
                        }
                    }*/
                    Orders::RejectOrder($input['order_id'], $input['reason'], $vendorAuthenticationId);

                    /* To get the customer authentication details */
                    $deviceDetail = Authentication_devices::getAuthenticationDeviceDetails($orderDetail->customer_authentication_id);
                    $customer_notitification_title = $customer_notitification_message = '';
                    if (!empty($deviceDetail) && isset($deviceDetail->fcm_token) && !empty($deviceDetail->fcm_token)) {
                        $customer_notitification_title = 'Order Rejected';
                        $customer_notitification_message = "Sorry! " . trim($orderDetail->VendorName) . " has rejected your order.";
                        $vendorNameAr = !empty(trim($orderDetail->VendorNameAr)) ? trim($orderDetail->VendorNameAr) : trim($orderDetail->VendorName);//
                        //if ($input['language'] == 'ar') {
                        $customer_notitification_title_ar = "تم رفض الطلب";
                        $customer_notitification_message_ar = "نأسف! " . $vendorNameAr . " رفض طلبك.";
                        //}
                        $receiver_role = 1;
                        $dataInput = ['sender_id' => $vendorAuthenticationId, 'sender_role' => 2, 'receiver_id' => $orderDetail->customer_authentication_id, 'receiver_role' => $receiver_role, 'created_by' => $vendorAuthenticationId];
                        $data_array = ['title' => $customer_notitification_title, 'title_ar' => $customer_notitification_title_ar, 'message' => $customer_notitification_message, 'message_ar' => $customer_notitification_message_ar, 'notification_type' => 7, 'order_id' => $input['order_id']];
                        /* To send the push notification */
                        Notifications::Send_notification($deviceDetail->device_type, $deviceDetail->fcm_token, $data_array, $dataInput);
                    }

                    /* To send the mail to customer */
                    $mail_array['name'] = $orderDetail->CustomerName;
                    $mail_array['email'] = $orderDetail->CustomerEmail;
                    $mail_array['subject'] = $customer_notitification_title;
                    $mail_array['message'] = $customer_notitification_message;
                    $mail_array['template'] = 'reject_order';
                    General_settings::sendMail($mail_array);
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
}
