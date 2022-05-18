<?php

namespace App\Http\V_1_0_0\VendorOrder\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\V_1_0_0\VendorOrder\Models\Order_status_masters;
use App\Http\V_1_0_0\VendorOrder\Models\Order_status_logs;
use DB;

class Orders extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'items_subtotal', 'grand_total', 'tax', 'delivery_charge', 'service_charge', 'vendor_discount_amount', 'promo_code_discount', 'collected_amount', 'payment_mode', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


    public static function OrderDetail($order_id, $vendor_id)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'customer_first_name as CustomerName', 'customer_last_name', 'vendor_first_name_en as VendorName', 'driver_first_name_en as DriverName', DB::raw('DATE_SUB(created_at, INTERVAL 90 MINUTE) as OrderDate'), 'grand_total as GRANDTOTAL', 'tax as TAX', 'delivery_charge as DeliveryCharge', 'promo_code as PromoCode', 'vendor_discount_amount as VendorDiscount', 'delivery_time', 'delivery_time_type', 'service_charge', 'collected_amount as CollectedAmount', 'payment_mode as PaymentMode', 'status as Status', 'status_id', 'order_instructions as  ChefInstruction', 'promo_code_discount')
            ->where('orders.id', $order_id)->where('orders.vendor_id', $vendor_id);
        return $checkOrder->first();
    }

    public static function OrderList($vendor_id, $key, $offset, $limit, $status, $from_date, $to_date)
    {
        $checkOrder = Orders::select('id as OrderId', 'akeed_order_id', 'customer_first_name as CustomerName', 'customer_last_name', 'vendor_first_name_en as VendorName', 'driver_first_name_en as DriverName', DB::raw('DATE_SUB(created_at, INTERVAL 90 MINUTE) as OrderDate'), 'grand_total as GrandTotal', 'tax as TAX', 'delivery_charge as DeliveryCharge', 'promo_code as PromoCode', 'vendor_discount_amount as VendorDiscount', 'delivery_time', 'delivery_time_type', 'collected_amount as CollectedAmount', 'payment_mode as PaymentMode', 'status as Status', 'status_id as StatusId')->where('status_id', '<>', 9)
            ->orderby('id', 'desc')
            ->where('vendor_id', '=', $vendor_id);

        if ($key != "") {
            $checkOrder->where(function ($query) use ($key) {
                $query->orWhere('akeed_order_id', 'LIKE', '%' . $key . '%')->orWhere('customer_first_name', 'LIKE', '%' . $key . '%')->orWhere('vendor_first_name_en', 'LIKE', '%' . $key . '%')->orWhere('driver_first_name_en', 'LIKE', '%' . $key . '%')->orWhere('status', 'LIKE', '%' . $key . '%')->orWhere('promo_code', 'LIKE', '%' . $key . '%');
            });
        }

        if ($status == "2") {
            $checkOrder->whereIn('status_id', array(2, 3));
        } elseif ($status == "8") {
            $checkOrder->where(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), '=', DB::raw('CONVERT(DATE_SUB(NOW(), INTERVAL 90 MINUTE), DATE)'));
        } elseif ($status == "9") {
            $checkOrder->where(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), '=', DB::raw('CONVERT(DATE_SUB(NOW(), INTERVAL 90 MINUTE), DATE)'))->whereNotIn('status_id', [6]);
        } elseif ($status != "" && $status != '0') {
            $checkOrder->where('status_id', $status);
        }

        if ($from_date != '' && $to_date != '') {
            $from_date = date("Y-m-d", strtotime($from_date)) . ' 00:00:00';
            $to_date = date("Y-m-d", strtotime($to_date)) . ' 23:59:59';
            $checkOrder->whereBetween(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), [DB::raw("CONVERT('" . $from_date . "', DATE)"), DB::raw("CONVERT('" . $to_date . "', DATE)")]);
        }

        $checkOrder = $checkOrder->skip($offset)->take($limit);

        return $checkOrder->get();
    }

    public static function ordercount($vendor_id)
    {
        return Orders::select('id')->where('status_id', '<>', 9)->where('vendor_id', '=', $vendor_id)->count();

    }

    public static function orderFiltercount($vendor_id, $key, $status, $from_date, $to_date)
    {
        $checkOrder = Orders::select('id')->where('status_id', '<>', 9)
            ->where('vendor_id', '=', $vendor_id);

        if ($key != "") {
            $checkOrder->where(function ($query) use ($key) {
                $query->where('akeed_order_id', 'LIKE', '%' . $key . '%')->orWhere('customer_first_name', 'LIKE', '%' . $key . '%')
                    ->orWhere('vendor_first_name_en', 'LIKE', '%' . $key . '%')->orWhere('driver_first_name_en', 'LIKE', '%' . $key . '%')->orWhere('status', 'LIKE', '%' . $key . '%')
                    ->orWhere('promo_code', 'LIKE', '%' . $key . '%');
            });
        }

        if ($status == "2") {
            $checkOrder->whereIn('status_id', array(2, 3));
        } elseif ($status == "8") {
            $checkOrder->where(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), '=', DB::raw('CONVERT(DATE_SUB(NOW(), INTERVAL 90 MINUTE), DATE)'));

        } else if ($status != "" && $status != '0') {
            $checkOrder->where('status_id', $status);
        }

        if ($from_date != '' && $to_date != '') {
            $from_date = date("Y-m-d", strtotime($from_date)) . ' 00:00:00';
            $to_date = date("Y-m-d", strtotime($to_date)) . ' 23:59:59';
            $checkOrder->whereBetween(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), [DB::raw("CONVERT('" . $from_date . "', DATE)"), DB::raw("CONVERT('" . $to_date . "', DATE)")]);
        }

        return $checkOrder->count();
    }

    public static function AccpetOrder($order_id, $reason, $authenticationId)
    {
        $order_statuslabel = Order_status_masters::find('2');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '2';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->order_accepted_time = DB::raw('now()');
        $checkOrder->reject_reason = $reason;
        $checkOrder->updated_by = $authenticationId;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authenticationId;
		$orderStatusLog->save();
    }

    public static function RejectOrder($order_id, $reject_reason, $authenticationId)
    {
        $order_statuslabel = Order_status_masters::find('7');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '7';
        $checkOrder->reject_reason = $reject_reason;
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->updated_by = $authenticationId;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authenticationId;
		$orderStatusLog->save();
    }

    public static function ReadyforDispatch($order_id, $reason, $authenticationId)
    {
        $order_statuslabel = Order_status_masters::find('4');
        $checkOrder = Orders::find($order_id);
        $checkOrder->status_id = '4';
        $checkOrder->status = $order_statuslabel->name;
        $checkOrder->ready_for_pickup_time = DB::raw('now()');
        $checkOrder->reject_reason = $reason;
        $checkOrder->updated_by = $authenticationId;
        $checkOrder->save();
		$orderStatusLog = new Order_status_logs;
		$orderStatusLog->order_id = $order_id;
		$orderStatusLog->akeed_order_id = $checkOrder->akeed_order_id;
		$orderStatusLog->status_id = $order_statuslabel->id;
		$orderStatusLog->status = $order_statuslabel->name;
		$orderStatusLog->created_by = $authenticationId;
		$orderStatusLog->save();
    }

    public static function orderStatus($vendor_id, $status_id)
    {
        $checkOrder = Orders::select('status_id', 'status', DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE) as created_at'))
            ->where('vendor_id', $vendor_id)->where('status_id', '<>', 9)
            ->where(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), '=', DB::raw('CONVERT(DATE_SUB(NOW(), INTERVAL 90 MINUTE), DATE)'));

        if ($status_id != '') {
            $checkOrder = $checkOrder->where('status_id', $status_id);
        }
        return $checkOrder->count();
    }

    public static function allOrderStatus($vendor_id, $status_id)
    {
        $checkOrder = Orders::select('status_id', 'status', DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE) as created_at'))
            ->where('vendor_id', $vendor_id)->where('status_id', $status_id);

        return $checkOrder->count();
    }

    public static function getRevenue($vendor_id, $pauyment_mode, $from_date, $to_date)
    {
        $orders = Orders::select('payment_mode', 'status')->where('status_id', '<>', 9);

        if ($from_date) {
            $date = '01-' . $from_date;
            $from_date = date('Y-m-d', strtotime($date)) . ' 00:00:00';
            $to_date = date('Y-m-t', strtotime($date)) . ' 23:59:59';
        } else {
            $from_date = date('Y-m-01') . ' 00:00:00';
            $to_date = date('Y-m-t') . ' 23:59:59';
        }
        $orders = $orders->whereBetween(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), [DB::raw("CONVERT('" . $from_date . "', DATE)"), DB::raw("CONVERT('" . $to_date . "', DATE)")])
            ->where('vendor_id', $vendor_id);

        if ($pauyment_mode != '') {
            $orders = $orders->whereIn('payment_mode', $pauyment_mode);
        }
        return $orders->sum('grand_total');
    }

    public static function getRevenueList($vendor_id, $pauyment_mode, $from_date)
    {
        $orders = Orders::select(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE) as date1'), DB::raw('sum(grand_total) as amount'))->where('status_id', '<>', 9);

        if ($from_date) {
            $date = '01-' . $from_date;
            $from_date = date('Y-m-d', strtotime($date)) . ' 00:00:00';
            $to_date = date('Y-m-t', strtotime($date)) . ' 23:59:59';
        } else {
            $from_date = date('Y-m-01') . ' 00:00:00';
            $to_date = date('Y-m-t') . ' 23:59:59';
        }

        $orders = $orders->whereBetween(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), [DB::raw("CONVERT('" . $from_date . "', DATE)"), DB::raw("CONVERT('" . $to_date . "', DATE)")])
            ->groupBy('date1')
            ->orderBy('date1', 'desc')
            ->where('vendor_id', $vendor_id)
            ->whereIn('payment_mode', $pauyment_mode);


        return $orders->get();

    }

    public static function getRevenueStatus($vendor_id, $status_id, $from_date, $to_date)
    {
        $orders = Orders::select('status_id', 'status', DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE) as created_at'))->where('status_id', '<>', 9);
        if ($from_date) {
            $date = '01-' . $from_date;
            $from_date = date('Y-m-d', strtotime($date)) . ' 00:00:00';
            $to_date = date('Y-m-t', strtotime($date)) . ' 23:59:59';
        } else {
            $from_date = date('Y-m-01') . ' 00:00:00';
            $to_date = date('Y-m-t') . ' 23:59:59';
        }
        $orders = $orders->whereBetween(DB::raw('CONVERT(DATE_SUB(created_at, INTERVAL 90 MINUTE),DATE)'), [DB::raw("CONVERT('" . $from_date . "', DATE)"), DB::raw("CONVERT('" . $to_date . "', DATE)")])
            ->where('vendor_id', $vendor_id);

        if ($status_id != '') {
            $orders = $orders->where('status_id', $status_id);
        }

        return $orders->count();
    }


}
