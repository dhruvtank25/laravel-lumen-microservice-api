<?php

namespace App\Http\V_1_0_0\VendorOrder\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Order_items extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'item_name_en', 'item_name_ar', 'description_en', 'description_ar', 'price', 'vendor_discount_amount', 'discount_amount','category_en','category_ar','type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function orderItem($order_id)
    {
        $orderItem = Orders::select('o.id as item_id', 'order_id', 'item_name_en as ITEMNAME', 'o.discounted_price',  'type', DB::raw('(o.discounted_price + COALESCE(addon_subtotal, 0)) AS Price'), 'o.is_addon', 'o.addon_names', 'o.primary_tag_name', 'o.bar_code', 'sku_code', 'language as LANGUAGE','quantity','orders.status as Status', 'payment_mode as PaymentMode','delivery_charge as DeliveryCharge', 'tax as TAX', 'service_charge as ServiceTax', 'orders.vendor_discount_amount', 'promo_code','o.subtotal AS TotalPrice')
            ->join('order_items as o', 'o.order_id', '=', 'orders.id')
            ->where('orders.id',$order_id);

        return $orderItem->get();
    }

}
