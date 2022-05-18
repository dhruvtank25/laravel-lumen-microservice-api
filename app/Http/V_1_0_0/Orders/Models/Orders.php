<?php

namespace App\Http\V_1_0_0\Orders\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\V_1_0_0\Orders\Models\Order_status_logs;
use App\Http\V_1_0_0\Orders\Models\Order_status_masters;

class Orders extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public static function getNewOrderList($vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'delivery_time_type', DB::raw('DATE_FORMAT(delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'), 'status_id', 'status', 'customer_id as UserId', 'customer_first_name as Name', 'items_subtotal', 'grand_total', DB::raw('DATE_FORMAT(DATE_SUB(created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at', 'order_read'))
            ->where('status_id', '1')->where('vendor_id', $vendor_id)->orderby('id', 'desc');
        return $checkOrder->get();
    }

    public static function getPastOrderList($vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'delivery_time_type', DB::raw('DATE_FORMAT(delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'), 'status_id', 'status', 'customer_id as UserId', 'customer_first_name as Name', 'items_subtotal', 'grand_total', DB::raw('DATE_FORMAT(DATE_SUB(created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at', 'order_read'))
            ->where('status_id', '6')->where('vendor_id', $vendor_id)->orderby('id', 'desc');
        return $checkOrder->get();
    }

    public static function getProcessingOrderList($vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'delivery_time_type', DB::raw('DATE_FORMAT(delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'), 'status_id', 'status', 'customer_id as UserId', 'customer_first_name as Name', 'items_subtotal', 'grand_total', DB::raw('DATE_FORMAT(DATE_SUB(created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at', 'order_read'))
            ->whereIn('status_id', array(2, 3, 8))->whereNull('ready_for_pickup_time')->where('vendor_id', $vendor_id);
        return $checkOrder->get();
    }

    public static function AccpetOrder($order_id, $reason, $authentication_id)
    {
        $order_statuslabel = Order_status_masters::find('2');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '2';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->order_accepted_time = DB::raw('now()');
        $checkOrder->reject_reason = $reason;
        $checkOrder->updated_by = $authentication_id;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authentication_id;
		$orderStatusLog->save();
		return true;
    }

    /* to rider assigned status */
    public static function RiderAssignedOrder($order_id, $driverDetail, $authentication_id)
    {
        $acceptOrder = Orders::find($order_id);

        $orderStatus = Order_status_masters::find(3);
        if ($acceptOrder->status_id == '2') {
            $acceptOrder->status_id = '3';
            $acceptOrder->status = $orderStatus->name;
        }
        $acceptOrder->driver_id = $driverDetail->id;
        $acceptOrder->driver_authentication_id = $driverDetail->authentication_id;
        $acceptOrder->driver_first_name_en = $driverDetail->firstname;
        $acceptOrder->driver_last_name_en = $driverDetail->lastname;
        $acceptOrder->driver_email = $driverDetail->email;
        $acceptOrder->driver_phone = $driverDetail->phone;
        $acceptOrder->driver_phone_country_code = $driverDetail->country_code;
        $acceptOrder->updated_by = $authentication_id;
        $acceptOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $acceptOrder->akeed_order_id;
		$orderStatusLog->status_id = $orderStatus->id;
		$orderStatusLog->status = $orderStatus->name;
		$orderStatusLog->created_by = $authentication_id;
		$orderStatusLog->save();
        return $acceptOrder;
    }

    public static function RejectOrder($order_id, $reject_reason, $authentication_id)
    {
        $order_statuslabel = Order_status_masters::find('7');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '7';
        $checkOrder->reject_reason = $reject_reason;
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->updated_by = $authentication_id;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authentication_id;
		$orderStatusLog->save();
		return true;
    }

    public static function ReadyforDispatch($order_id, $reason, $authentication_id)
    {
        $order_statuslabel = Order_status_masters::find('4');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '4';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->ready_for_pickup_time = DB::raw('now()');
        $checkOrder->reject_reason = $reason;
        $checkOrder->updated_by = $authentication_id;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authentication_id;
		$orderStatusLog->save();
		return true;
    }

    public static function ReadyforDispatchlist($vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'delivery_time_type', 'delivery_time', 'status_id', 'status', 'customer_id as UserId', 'customer_first_name as Name', 'items_subtotal', 'grand_total', DB::raw('DATE_FORMAT(orders.delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'), DB::raw('DATE_FORMAT(DATE_SUB(created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at', 'order_read'))
            ->whereIn('status_id', array(3, 4, 8))->whereNotNull('ready_for_pickup_time')->where('vendor_id', $vendor_id)->orderby('id', 'desc');
        return $checkOrder->get();
    }

    public static function OrderDetail($order_id, $vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'items_subtotal as SUBTOTAL', 'delivery_charge as DELIVERYCHARGE',
            'tax as TAX', 'grand_total as GRANDTOTAL', 'order_instructions as  INSTRUCTIONS', 'translated_order_instructions',
            'delivery_instructions as DRIVERINSTRUCTION', 'delivery_time_type', DB::raw('DATE_FORMAT(delivery_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivery_time'),
            DB::raw('DATE_SUB(orders.created_at, INTERVAL 90 MINUTE) as CREATEDON'), 'customer_mobile as MOBILENO', 'status as STATUSNAME',
            'status_id as STATUSID', 'customer_id', 'customer_authentication_id', 'vendor_authentication_id', 'promo_code as PROMOCODE', 'promo_code_discount', 'discount_amount as DISCOUNTAMOUNT',
            'service_charge as SERVICETAX', 'akeed_order_id', DB::raw("TRIM(CONCAT(IFNULL(customer_first_name,''),' ',IFNULL(customer_last_name,''))) AS CustomerName"),
            'customer_email as CustomerEmail', 'customer_mobile as CustomerMobile', 'customer_address', 'vendor_first_name_en as VendorName', 'vendor_first_name_ar as VendorNameAr',
            'vendor_email as VendorEmail', 'vendor_phone', 'driver_id', DB::raw("TRIM(CONCAT(IFNULL(driver_first_name_en,''),' ',IFNULL(driver_last_name_en,''))) AS DriverName"),
            'driver_email', 'driver_phone', 'customer_latitude', 'customer_longitude', 'status_id', 'driver_authentication_id',
            DB::raw('DATE_FORMAT(DATE_SUB(orders.created_at, INTERVAL 90 MINUTE), "%d-%m-%Y") as formated_created_at'),
            DB::raw('DATE_FORMAT(DATE_SUB(orders.created_at, INTERVAL 90 MINUTE), "%d-%m-%Y %h:%i:%s %p") as order_created_time'),
            DB::raw("(SELECT SUM(order_items.quantity) FROM order_items WHERE order_items.order_id = orders.id) as quantity_count"),
            'payment_mode as PAYMENTMODE', 'one_click_order', DB::raw('DATE_FORMAT(delivered_time, "%d-%m-%Y %h:%i:%s %p") as formated_delivered_time'),
            'orders.order_accepted_time', 'orders.ready_for_pickup_time',
            DB::raw('DATE_FORMAT(order_accepted_time, "%d-%m-%Y %h:%i:%s %p") as formated_order_accepted_time'),
            'vendor_rating', 'order_review', 'driver_rating', 'order_read')
            ->where('orders.id', $order_id)->where('orders.vendor_id', $vendor_id);
        return $checkOrder->first();
    }

    public static function OrderList($vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', DB::raw("TRIM(CONCAT(IFNULL(customer_first_name,''),' ',IFNULL(customer_last_name,''))) AS CustomerName"), 'vendor_first_name_en as VendorName', DB::raw("TRIM(CONCAT(IFNULL(driver_first_name_en,''),' ',IFNULL(driver_last_name_en,''))) AS DriverName"), DB::raw('DATE_SUB(orders.created_at, INTERVAL 90 MINUTE) as OrderDate'), 'grand_total as GrandTotal', 'tax as TAX', 'delivery_charge as DeliveryCharge', 'promo_code as PromoCode', 'vendor_discount_amount as VendorDiscount', 'delivery_date as DELIVERYDATE', 'orders.delivery_time as DeliveryTime', 'delivery_time_type', 'collected_amount as CollectedAmount', 'payment_mode as PaymentMode', 'status as Status', 'status_id as StatusId', 'order_read')->where('status_id', '<>', 9)->where('vendor_id', '=', $vendor_id);

        return $checkOrder->get();
    }

    public static function geDriverDetails($order_id)
    {
        $driver = Orders::select('driver_id')
            ->where('id', $order_id);

        return $driver->first();
    }

    public static function getCustomerDetails($order_id)
    {
        $customer = Orders::select('customer_id')
            ->where('id', $order_id);

        return $customer->first();
    }
}
