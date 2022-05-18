<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Order_items extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function orderItem($order_id)
    {
        $orderItem = Orders::select('o.id as item_id', 'order_id', 'item_name_en as ITEMNAME', 'price',  'type','o.subtotal AS TotalPrice', 'o.is_addon', 'o.addon_names as addons', 'o.addon_subtotal', 'o.primary_tag_name as MENUCATEGORYNAME', 'o.bar_code', 'sku_code', 'language as LANGUAGE','quantity','orders.status as Status', 'payment_mode as PaymentMode','delivery_charge as DeliveryCharge', 'tax as TAX', 'service_charge as ServiceTax', 'orders.vendor_discount_amount', 'promo_code')
            ->join('order_items as o', 'o.order_id', '=', 'orders.id')
            ->where('orders.id',$order_id);

        return $orderItem->get();
    }

}
