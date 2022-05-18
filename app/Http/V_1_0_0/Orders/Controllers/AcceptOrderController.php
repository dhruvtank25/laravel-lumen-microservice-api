<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;

use App\Http\V_1_0_0\Orders\Models\Orders;
use App\Http\V_1_0_0\Orders\Models\Order_items;
use App\Http\V_1_0_0\Notifications\Models\Notifications;
use App\Http\V_1_0_0\Orders\Models\Drivers;
use App\Http\V_1_0_0\Orders\Models\Authentication_devices;
use App\Http\V_1_0_0\GlobalSettings\Models\General_settings;
use App\Helpers\ResponseBuilder;

//use Cache;

class AcceptOrderController extends Controller
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
     * accept order api
     *
     * @return \Illuminate\Http\Response
     */
    public function accpetOrder(Request $request)
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
                if ($orderDetail->STATUSID == 6) {
                    return ResponseBuilder::responseResult($this->failureStatus, 'This order is already delivered.');
                }
                if ($orderDetail->STATUSID == 7) {
                    return ResponseBuilder::responseResult($this->failureStatus, 'This order is already rejected.');
                }
                if (empty($orderDetail->order_accepted_time)) {
                    /*$client = new \GuzzleHttp\Client();
                    if (!empty($orderDetail->akeed_order_id)) {

                        $response = $client->request('POST', env('DOT_NET_BASE_URL') . '/Monolith/VendorOrderStatusChanges', ['form_params' => [
                            'OrderId' => $orderDetail->akeed_order_id,
                            'Status' => 3,//3-Accept order
                            'Reason' => $input['reason']
                        ]]);
                        $contents = $response->getBody()->getContents();
                        //if ($contents <> 'record updated successfully') {
                            //return ResponseBuilder::responseResult($this->failureStatus, 'Order Status not changed, because of monolithic issue.');
                        //}
                    } */
                    Orders::AccpetOrder($input['order_id'], $input['reason'], $vendorAuthenticationId);

                    /* To send the push notification to the customer */
                    $customer_notitification_title = 'Order Confirmed';
                    $customer_notitification_message = 'Super! ' . trim($orderDetail->VendorName) . ' has confirmed your order and is preparing your food.';
                    $vendorNameAr = !empty(trim($orderDetail->VendorNameAr)) ? trim($orderDetail->VendorNameAr) : trim($orderDetail->VendorName);
                    //if ($input['language'] == 'ar') {
                    $customer_notitification_title_ar = "تم تأكيد طلبك";
                    $customer_notitification_message_ar = "ممتاز! " . $vendorNameAr . " لقد أكد طلبك ويقوم بإعداد طعامك.";
                    //}
                    /* To get the customer authentication details */
                    $deviceDetail = Authentication_devices::getAuthenticationDeviceDetails($orderDetail->customer_authentication_id);
                    if (!empty($deviceDetail) && isset($deviceDetail->fcm_token) && !empty($deviceDetail->fcm_token)) {
                        $receiver_role = 1;
                        $dataInput = ['sender_id' => $orderDetail->vendor_authentication_id, 'sender_role' => 2, 'receiver_id' => $orderDetail->customer_authentication_id, 'receiver_role' => $receiver_role, 'created_by' => $orderDetail->vendor_authentication_id];
                        $data_array = ['title' => $customer_notitification_title, 'title_ar' => $customer_notitification_title_ar, 'message' => $customer_notitification_message, 'message_ar' => $customer_notitification_message_ar, 'notification_type' => 2, 'order_id' => $input['order_id']];
                        /* To send the push notification */
                        Notifications::Send_notification($deviceDetail->device_type, $deviceDetail->fcm_token, $data_array, $dataInput);
                    }

                    $order_detail_template = "<table style='margin-left:20%;'><tbody>";
                    $order_detail_template .= "<tr><td>Order Id</td><td>" . $orderDetail->akeed_order_id . "</td></tr>";
                    /* To get the order item detail */
                    $orderItems = Order_items::orderItem($orderDetail->OrderId);
                    foreach ($orderItems as $o_items) {
                        $addons = '';
                        if (!empty($o_items->addons)) {
                            $addons = "(" . $o_items->addons . ")";
                        }
                        $order_detail_template .= "<tr><td>" . $o_items->ITEMNAME . $addons . " X  " . $o_items->quantity . "</td><td>" . "OMR " . $o_items->price . "</td></tr>";
                    }
                    $order_detail_template .= "</tbody></table>";
                    $order_detail_template .= "<table style='margin-left:41%;'><tbody><tr style='border-bottom: 1px solid #c5c5c5;'> <td> Sub Total </td><td><span> OMR " . $orderDetail->SUBTOTAL . " </span></td></tr><tr style='border-bottom: 1px solid #c5c5c5;'> <td> Delivery Charge </td><td><span> OMR " . $orderDetail->DELIVERYCHARGE . " </span></td></tr><tr style = 'border-bottom: 1px solid #c5c5c5;'><td> Municipality Tax </td><td><span> OMR " . $orderDetail->TAX . " </span></td></tr><tr style='border-bottom: 1px solid #c5c5c5;'><td> Tourism Tax </td><td><span> OMR " . $orderDetail->SERVICETAX . "</span></td></tr><tr><td> Grand Total </td><td><span> OMR " . $orderDetail->GRANDTOTAL . " </span></td></tr></tbody></table>";
                    /* To send the mail to customer */
                    $mail_array['name'] = $orderDetail->CustomerName;
                    $mail_array['email'] = $orderDetail->CustomerEmail;
                    $mail_array['subject'] = 'Order Confirmed! Order Id - ' . $orderDetail->akeed_order_id;
                    $mail_array['message'] = $customer_notitification_message;
                    $mail_array['order_details'] = $order_detail_template;
                    $mail_array['template'] = 'accept_order';
                    General_settings::sendMail($mail_array);

                    if ($orderDetail->one_click_order <> 'Y') {
                        $orderId = !empty($orderDetail->akeed_order_id) ? $orderDetail->akeed_order_id : $input['order_id'];
                        /* To send the push notification to the driver */
                        $driverList = Drivers::getAllDriversDeviceDetail();//print_r($driverList);die;
                        $data_array = array();
                        if (!empty($driverList)) {
                            $delivery_date = $orderDetail->formated_delivery_time;
                            if ($orderDetail->delivery_time_type == 'as_soon_as') {
                                $delivery_date = $orderDetail->formated_created_at . ' / As soon as possible';
                            }
                            $driver_notitification_title = 'Driver Notification';
                            $driver_notitification_message = 'New Order from ' . trim($orderDetail->VendorName) . ' #' . $orderId;
                            //To get the customer authentication details
                            $receiver_role = 3;
                            $data_array = ['title' => $driver_notitification_title, 'message' => $driver_notitification_message, 'notification_type' => 2, 'order_id' => $input['order_id'], 'akeed_order_id' => $orderId, 'delivery' => $delivery_date, 'location' => $orderDetail->customer_address, 'instruction' => $orderDetail->DRIVERINSTRUCTION, 'Force' => 0, 'Rname' => $orderDetail->VendorName, 'PAYMENTMODE' => $orderDetail->PAYMENTMODE, 'receiver_role' => $receiver_role];
                            $android_device_token = $ios_device_token = array();
                            foreach ($driverList as $driver) {
                                if (!empty($driver->fcm_token)) {
                                    if ($driver->device_type == 'android') {
                                        $android_device_token[] = $driver->fcm_token;
                                    } elseif ($driver->device_type == 'ios') {
                                        $ios_device_token[] = $driver->fcm_token;
                                    }
                                }
                            }
                            //To send the push notification
                            if (!empty($android_device_token)) {
                                Notifications::SendGroupNotification('android', $android_device_token, $data_array);
                            }
                            if (!empty($ios_device_token)) {
                                Notifications::SendGroupNotification('ios', $ios_device_token, $data_array);
                            }
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
}
