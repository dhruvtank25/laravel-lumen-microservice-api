<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Order_status_masters;
use DB;

class Orders extends Model
{
    public static function OrderDetail($order_id)
    {
        $checkOrder = Orders::select('id')->where('orders.akeed_order_id', $order_id);
        return $checkOrder->first();
    }

    public static function newOrderDetail($order_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'items_subtotal as SUBTOTAL', 'delivery_charge as DELIVERYCHARGE', 'tax as TAX', 'grand_total as GRANDTOTAL', 'order_instructions as  INSTRUCTIONS', 'delivery_instructions as DRIVERINSTRUCTION', 'delivery_time_type', DB::raw('DATE_FORMAT(delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'), DB::raw('DATE_SUB(orders.created_at, INTERVAL 90 MINUTE) as CREATEDON'), 'customer_mobile as MOBILENO', 'status as STATUSNAME', 'status_id as STATUSID', 'customer_id', 'customer_authentication_id', 'vendor_authentication_id', 'promo_code as PROMOCODE', 'discount_amount as DISCOUNTAMOUNT', 'service_charge as SERVICETAX', 'akeed_order_id', 'customer_first_name as CustomerName', 'customer_email as CustomerEmail', 'customer_mobile as CustomerMobile', 'customer_address', 'vendor_first_name_en as VendorName', 'vendor_first_name_ar as VendorNameAr', 'vendor_email as VendorEmail', 'vendor_phone', 'driver_id', DB::raw("TRIM(CONCAT(IFNULL(driver_first_name_en,''),' ',IFNULL(driver_last_name_en,''))) AS DriverName"), 'driver_email', 'driver_phone', 'customer_latitude', 'customer_longitude', 'status_id', 'driver_authentication_id', DB::raw('DATE_FORMAT(DATE_SUB(orders.created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at'), DB::raw('DATE_FORMAT(DATE_SUB(orders.created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at'), DB::raw('DATE_FORMAT(DATE_SUB(orders.created_at, INTERVAL 90 MINUTE), "%d-%m-%Y %h:%i:%s %p") as order_created_time'), DB::raw("(SELECT SUM(order_items.quantity) FROM order_items WHERE order_items.order_id = orders.id) as quantity_count"), 'payment_mode as PAYMENTMODE', 'one_click_order')
            ->where('orders.id', $order_id);
        return $checkOrder->first();
    }

    public static function AccpetOrder($order_id)
    {
        $order_statuslabel = Order_status_masters::find('2');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '2';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->order_accepted_time = DB::raw('now()');
        $checkOrder->save();
        return $checkOrder;
    }

    public static function ReadyforDispatch($order_id, $driver_tracking_link)
    {
        $order_statuslabel = Order_status_masters::find('4');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '4';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->ready_for_pickup_time = DB::raw('now()');
        if (!empty($driver_tracking_link)) {
            $checkOrder->driver_tracking_link = $driver_tracking_link;
        }
        $checkOrder->save();
    }

    public static function RejectOrder($order_id, $reject_reason)
    {
        $order_statuslabel = Order_status_masters::find('7');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '7';
        $checkOrder->reject_reason = $reject_reason;
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->save();
    }

    public static function Insert_Order($input)
    {
        $orders = new Orders;
        $orders->customer_authentication_id = $input['customer_authentication_id'];
        $orders->items_subtotal = $input['items_subtotal'];
        $orders->grand_total = $input['grand_total'];
        $orders->tax = $input['tax'];
        $orders->delivery_charge = $input['delivery_charge'];
        $orders->service_charge = $input['service_charge'];
        $orders->payment_mode = $input['paymentmode'];
        /*if ($input['paymentmode'] == '1') {
            $orders->payment_mode = 'Cash On Delivery';
        } elseif ($input['paymentmode'] == '2') {
            $orders->payment_mode = 'Card On Delivery';
        } elseif ($input['paymentmode'] == '3') {
            $orders->payment_mode = 'Credit Card';
        } elseif ($input['paymentmode'] == '4') {
            $orders->payment_mode = 'Debit Card';
        } elseif ($input['paymentmode'] == '5') {
            $orders->payment_mode = 'No Payment';
        }*/
        $orders->transactionId = isset($input['transactionid']) ? $input['transactionid'] : 0;
        $orders->promo_code_discount = $input['discount_amount'];
        $orders->vendor_discount_amount = $input['vendor_discount_amount'];
        $orders->promo_code = $input['promo_code'];
        $orders->promo_code_discount_type = $input['promo_code_discount_type'];
        $orders->promo_code_discount_amount = $input['promo_code_discount_amount'];
        $orders->promo_code_discount_percentage = $input['promo_code_discount_percentage'];
        $orders->customer_id = $input['customer_id'];
        $orders->customer_first_name = $input['customer_first_name'];
        $orders->customer_last_name = $input['customer_last_name'];
        $orders->customer_mobile = $input['customer_mobile'];
        $orders->customer_order_mobile = $input['customer_order_mobile'];
        $orders->customer_email = $input['customer_email'];
        $orders->customer_house_no = $input['customer_house_no'];
        $orders->customer_address = $input['customer_address'];
        $orders->customer_landmark = $input['customer_landmark'];
        $orders->customer_address_type = $input['customer_address_type'];
        $orders->customer_address_id = $input['customer_address_id'];
        $orders->customer_latitude = $input['customer_latitude'];
        $orders->customer_longitude = $input['customer_longitude'];
        $orders->customer_address_latitude = $input['customer_address_latitude'];
        $orders->customer_address_longitude = $input['customer_address_longitude'];
        $orders->customer_address_delivery_note = $input['customer_address_delivery_note'];
        $orders->deliverydistance = $input['deliverydistance'];
        $orders->preparationtime = $input['preparationtime'];
        $orders->order_instructions = $input['order_instructions'];
        $orders->delivery_instructions = $input['delivery_instruction'];
        $orders->delivery_time_type = $input['delivery_time_type'];
        if ($input['delivery_time_type'] != 'as_soon_as') {
            $orders->delivery_time = $input['delivery_time'];
        }
        $orders->vendor_id = $input['vendor_id'];
        $orders->vendor_authentication_id = $input['vendor_authentication_id'];
        $orders->vendor_first_name_en = $input['vendor_first_name_en'];
        $orders->vendor_first_name_ar = $input['vendor_first_name_ar'];
        $orders->vendor_email = $input['vendor_email'];
        $orders->vendor_phone = $input['vendor_phone'];
        $orders->vendor_address = $input['vendor_address'];
        $orders->vendor_landmark = $input['vendor_landmark'];
        $orders->vendor_latitude = $input['vendor_latitude'];
        $orders->vendor_longitude = $input['vendor_longitude'];
        $orders->municipal_tax_percentage = $input['municipal_tax_percentage'];
        $orders->service_charge_percentage = $input['service_charge_percentage'];
        $orders->vendor_akeed_percentage = $input['vendor_akeed_percentage'];
        $orders->vendor_min_order_discount = $input['vendor_min_order_discount'];
        $orders->vendor_discount_type = $input['vendor_discount_type'];
        $orders->vendor_discount_type_amount = $input['vendor_discount_type_amount'];
        $orders->vendor_discount_type_percentage = $input['vendor_discount_type_percentage'];
        $orders->vendor_discount_active_from = $input['vendor_discount_active_from'];
        $orders->vendor_discount_active_to = $input['vendor_discount_active_to'];
        $orders->status_id = $input['status_id'];
        $orders->status = $input['status'];
        $orders->akeed_order_id = $input['akeed_order_id'];
        $orders->save();
        return $orders->id;
    }
}
