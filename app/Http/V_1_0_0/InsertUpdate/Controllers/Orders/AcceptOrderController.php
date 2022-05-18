<?php

namespace App\Http\V_1_0_0\InsertUpdate\Controllers\Orders;

use Illuminate\Http\Request;
use App\Http\Controller;

use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Orders;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Vendors;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Customers;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Authentications;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Authentication_devices;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Customer_address_masters;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Menu_items;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Order_items;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Promocodes;
use App\Http\V_1_0_0\Notifications\Models\Notifications;
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
            $input['orderid'] = isset($input['orderid']) ? $input['orderid'] : '';
            $orderDetail = Orders::OrderDetail($input['orderid']);
            if (empty($orderDetail)) {
                /* To check the input values */
                $input['items_subtotal'] = isset($input['items_subtotal']) ? trim($input['items_subtotal']) : '';
                $input['grand_total'] = isset($input['grand_total']) ? trim($input['grand_total']) : '';
                $input['tax'] = isset($input['tax']) ? trim($input['tax']) : '';
                $input['delivery_charge'] = isset($input['delivery_charge']) ? trim($input['delivery_charge']) : '';
                $input['service_charge'] = isset($input['service_charge']) ? trim($input['service_charge']) : '';
                $input['discount_amount'] = isset($input['discount_amount']) ? trim($input['discount_amount']) : '';
                $input['vendor_discount_amount'] = isset($input['vendor_discount_amount']) ? trim($input['vendor_discount_amount']) : '';
                $input['promo_code'] = isset($input['promo_code']) ? trim($input['promo_code']) : '';
                $input['customer_id'] = isset($input['customer_id']) ? trim($input['customer_id']) : '';
                $input['customer_email'] = isset($input['EMAILID']) ? trim($input['EMAILID']) : '';
                $input['customer_first_name'] = isset($input['FIRSTNAME']) ? trim($input['FIRSTNAME']) : '';
                $input['customer_last_name'] = isset($input['LASTNAME']) ? trim($input['LASTNAME']) : '';
                $input['customer_password'] = isset($input['PASSWORD']) ? trim($input['PASSWORD']) : '';
                $input['customer_mobile'] = isset($input['customermobileno']) ? trim($input['customermobileno']) : '';
                $input['customer_order_mobile'] = isset($input['mobileno']) ? trim($input['mobileno']) : '';
                $input['customer_gender'] = isset($input['gender']) ? trim($input['gender']) : '';
                $input['customer_dob'] = isset($input['dob']) ? trim($input['dob']) : '';
                $input['customer_address_id'] = isset($input['customer_address_id']) ? trim($input['customer_address_id']) : '';
                $input['customer_address_type'] = isset($input['TYPE']) ? trim($input['TYPE']) : '';
                $input['customer_house_no'] = isset($input['houseno']) ? trim($input['houseno']) : '';
                $input['customer_landmark'] = isset($input['landmark']) ? trim($input['landmark']) : '';
                $input['customer_address'] = isset($input['ADDRESSName']) ? trim($input['ADDRESSName']) : '';
                $input['customer_address_latitude'] = isset($input['latitude']) ? trim($input['latitude']) : '';
                $input['customer_address_longitude'] = isset($input['longitude']) ? trim($input['longitude']) : '';
                $input['customer_address_delivery_note'] = isset($input['deliverynote']) ? trim($input['deliverynote']) : '';
                $input['paymentmode'] = isset($input['paymentmode']) ? trim($input['paymentmode']) : '';
                $input['order_instructions'] = isset($input['order_instructions']) ? trim($input['order_instructions']) : '';
                $input['delivery_instruction'] = isset($input['delivery_instruction']) ? trim($input['delivery_instruction']) : '';
                $input['delivery_date'] = isset($input['delivery_date']) ? trim($input['delivery_date']) : '';
                $input['delivery_time'] = isset($input['delivery_time']) ? trim($input['delivery_time']) : '';
                $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
                $input['akeed_order_id'] = isset($input['orderid']) ? trim($input['orderid']) : '';
                $input['lat'] = isset($input['lat']) ? trim($input['lat']) : '0.0';
                $input['longi'] = isset($input['longi']) ? trim($input['longi']) : '0.0';
                $input['deliverydistance'] = isset($input['deliverydistance']) ? trim($input['deliverydistance']) : '';
                $input['preparationtime'] = isset($input['preparationtime']) ? trim($input['preparationtime']) : '';

                $input['status_id'] = 1;
                $input['status'] = 'NEW ORDER';
                $promo_code = '';
                $promo_code_discount_type = null;
                $promo_code_discount_amount = $promo_code_discount_percentage = 0;
                if (isset($input['promo_code']) && !empty($input['promo_code'])) {
                    $promoCodeDetail = Promocodes::getPromoCodeDetails($input['promo_code']);
                    if (!empty($promoCodeDetail)) {
                        $promo_code = $input['promo_code'];
                        $promo_code_discount_type = ($promoCodeDetail->discount_type == 1) ? 'amount' : 'percentage';
                        $promo_code_discount_amount = $promoCodeDetail->discount_amount;
                        $promo_code_discount_percentage = $promoCodeDetail->discount_percent;
                    }
                }
                $input['promo_code'] = $promo_code;
                $input['promo_code_discount_type'] = $promo_code_discount_type;
                $input['promo_code_discount_amount'] = $promo_code_discount_amount;
                $input['promo_code_discount_percentage'] = $promo_code_discount_percentage;

                $vendor = Vendors::getVendorDetails($input['vendor_id']);//this vendor_id is akeed_user_id

                $input['vendor_id'] = $vendor->id;
                $input['vendor_authentication_id'] = $vendor->authentication_id;
                $input['vendor_first_name_en'] = $vendor->shop_name_en;
                $input['vendor_first_name_ar'] = $vendor->shop_name_ar;
                $input['vendor_address'] = $vendor->address_1;
                $input['vendor_landmark'] = $vendor->landmark;
                $input['vendor_latitude'] = $vendor->latitude;
                $input['vendor_longitude'] = $vendor->longitude;
                $input['municipal_tax_percentage'] = $vendor->municipal_tax;
                $input['service_charge_percentage'] = $vendor->service_charge;
                $input['vendor_akeed_percentage'] = $vendor->akeed_percentage;
                $input['vendor_min_order_discount'] = $vendor->min_order_discount;
                $input['vendor_discount_type'] = $vendor->discount_type;
                $input['vendor_discount_type_amount'] = $vendor->discount_amount;
                $input['vendor_discount_type_percentage'] = $vendor->discount_percentage;
                $input['vendor_discount_active_from'] = $vendor->discount_active_from;
                $input['vendor_discount_active_to'] = $vendor->discount_active_to;
                //$authentication_id = $vendor->authentication_id;

                //$vendor_auth = Authentications::getAuthenticationDetails($authentication_id);
                $input['vendor_phone'] = $vendor->phone;
                $input['vendor_email'] = $vendor->email;

                $customer = Customers::getCustomerDetails($input['customer_id']);
                if (!empty($customer)) {
                    $input['customer_id'] = $customer->id;
                    $input['customer_authentication_id'] = $customer->authentication_id;
                    $input['customer_first_name'] = $customer->firstname;
                    $input['customer_last_name'] = $customer->lastname;
                    $input['customer_order_mobile'] = !empty($input['customer_order_mobile']) ? $input['customer_order_mobile'] : $customer->phone;
                    $input['customer_mobile'] = $customer->phone;
                    $input['customer_email'] = $customer->email;
                } else {
                    /* To insert the authentication detail */
                    $authenticationId = Authentications::insertAuthentication($input);
                    $input['customer_authentication_id'] = $authenticationId;
                    /* To insert the customer detail */
                    $customerId = Customers::insertCustomer($input);
                    $input['customer_id'] = $customerId;
                    /* To insert the authentication devices */
                    Authentication_devices::Insert_devices($input);
                }
                $input['customer_latitude'] = $input['longi'];
                $input['customer_longitude'] = $input['lat'];

                $address = Customer_address_masters::getAddres($input['customer_address_id']);
                /* To check the customer address is available or not */
                if (!empty($address)) {
                    $input['customer_address_id'] = $address->id;
                    $input['customer_address_type'] = $address->address_type;
                    $input['customer_house_no'] = $address->house_no;
                    $input['customer_landmark'] = $address->landmark;
                    $input['customer_address'] = $address->address;
                    $input['customer_address_latitude'] = $address->latitude;
                    $input['customer_address_longitude'] = $address->longitude;
                    $input['customer_address_delivery_note'] = $address->delivery_note;
                } else {
                    /* To insert the customer address */
                    $addressId = Customer_address_masters::insertCustomerAddress($input);
                    $input['customer_address_id'] = $addressId;
                }

                if (strtolower($input['delivery_time']) == 'as soon as possible') {
                    $input['delivery_time_type'] = 'as_soon_as';
                    $input['delivery_time'] = null;
                } else {
                    $input['delivery_time_type'] = 'delivery_time';
                    $input['delivery_time'] = date("Y-m-d H:i:s", strtotime($input['delivery_date'] . ' ' . $input['delivery_time']));
                }
                $order_id = Orders::Insert_Order($input);
                $m_items['order_id'] = $order_id;
                $m_items['customer_id'] = $input['customer_id'];
                $m_items['customer_authentication_id'] = $input['customer_authentication_id'];
                $m_items['vendor_id'] = $input['vendor_id'];
                $m_items['vendor_authentication_id'] = $input['vendor_authentication_id'];
                foreach ($input['orderdetails'] as $items) {
                    $m_items['akeed_product_id'] = isset($items['PRODUCTID']) ? trim($items['PRODUCTID']) : '';
                    $menuItemDetails = Menu_items::getMenuItemDetail($m_items['akeed_product_id']);
                    $m_items['akeed_order_item_id'] = isset($items['orderdetailid']) ? trim($items['orderdetailid']) : '';
                    $m_items['item_name_en'] = isset($items['ITEMNAME']) ? trim($items['ITEMNAME']) : '';
                    $m_items['quantity'] = isset($items['QUANTITY']) ? trim($items['QUANTITY']) : 0;
                    $m_items['addonsubtotal'] = isset($items['addonsubtotal']) ? trim($items['addonsubtotal']) : 0;
                    $m_items['discounted_price'] = $m_items['price'] = $price = 0;
                    if (isset($items['PRICE'])) {
                        $price = ($items['PRICE'] - $m_items['addonsubtotal']);
                        $f_price = ($price > 0 && $items['QUANTITY'] > 0) ? ($price / $items['QUANTITY']) : 0;
                        $m_items['discounted_price'] = $m_items['price'] = $f_price;
                    }
                    $m_items['subtotal'] = isset($items['PRICE']) ? trim($items['PRICE']) : 0;
                    $m_items['type'] = isset($items['TYPE']) ? trim($items['TYPE']) : null;
                    $m_items['addon_names'] = isset($items['ADDON']) ? trim($items['ADDON']) : '';
                    $order_item_id = Order_items::Insert_orderItems($m_items, $menuItemDetails);
                }

                /* To send the push notification to the customer */
                $customer_notitification_title = 'Order Received';
                $customer_notitification_message = 'Your order ' . $input['akeed_order_id'] . ' has been received by us and is currently being processed by ' . trim($input['vendor_first_name_en']) . '. We are waiting for confirmation!';
                $vendorNameAr = !empty(trim($input['vendor_first_name_ar'])) ? trim($input['vendor_first_name_ar']) : trim($input['vendor_first_name_en']);
                //if (isset($input['language']) && $input['language'] == 'ar') {
                $customer_notitification_title_ar = 'طلب وارد';
                $customer_notitification_message_ar = "طلبك " . $input['akeed_order_id'] . "  تم استلامه بنجاح و حاليا يتم تنفيذه بواسطة" . $vendorNameAr . "! في انتظار التأكيد";
                //}

                /* To get the admin details */
                $adminDetail = Authentications::getAdminDetail(5);//5-Super Admin role
                /* To get the customer authentication details */
                $deviceDetail = Authentication_devices::authenticationDeviceDetail($input['customer_authentication_id']);
                if (!empty($deviceDetail)) {
                    $receiver_role = 1;
                    $dataInput = ['sender_id' => $adminDetail->id, 'sender_role' => 5, 'receiver_id' => $input['customer_authentication_id'], 'receiver_role' => $receiver_role];
                    $dataArray = ['title' => $customer_notitification_title, 'title_ar' => $customer_notitification_title_ar, 'message' => $customer_notitification_message, 'message_ar' => $customer_notitification_message_ar, 'receiver_id' => $input['customer_authentication_id']];
                    /* To send the push notification */
                    Notifications::Send_notification($deviceDetail->device_type, $deviceDetail->fcm_token, $dataInput, $dataArray);
                }
            } else {
                $order_id = $orderDetail->id;
            }
            Orders::AccpetOrder($order_id);
            $newOrderDetail = Orders::newOrderDetail($order_id);
            /* To send the push notification to the customer */
            $customer_notitification_title = 'Order Confirmed';
            $customer_notitification_message = 'Super! ' . trim($newOrderDetail->VendorName) . ' has confirmed your order and is preparing your food.';
            $vendorNameAr = !empty(trim($newOrderDetail->VendorNameAr)) ? trim($newOrderDetail->VendorNameAr) : trim($newOrderDetail->VendorName);
            //if ($input['language'] == 'ar') {
            $customer_notitification_title_ar = "تم تأكيد طلبك";
            $customer_notitification_message_ar = "ممتاز! " . $vendorNameAr . " لقد أكد طلبك ويقوم بإعداد طعامك.";
            //}
            /* To get the customer authentication details */
            $deviceDetail = Authentication_devices::authenticationDeviceDetail($newOrderDetail->customer_authentication_id);
            if (!empty($deviceDetail) && isset($deviceDetail->fcm_token) && !empty($deviceDetail->fcm_token)) {
                $receiver_role = 1;
                $dataInput = ['sender_id' => $newOrderDetail->vendor_authentication_id, 'sender_role' => 2, 'receiver_id' => $newOrderDetail->customer_authentication_id, 'receiver_role' => $receiver_role, 'created_by' => $newOrderDetail->vendor_authentication_id];
                $data_array = ['title' => $customer_notitification_title, 'title_ar' => $customer_notitification_title_ar, 'message' => $customer_notitification_message, 'message_ar' => $customer_notitification_message_ar, 'notification_type' => 2, 'order_id' => $newOrderDetail->id];
                /* To send the push notification */
                Notifications::Send_notification($deviceDetail->device_type, $deviceDetail->fcm_token, $data_array, $dataInput);
            }
            return ResponseBuilder::responseResult($this->successStatus, 'record updated successfully');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
