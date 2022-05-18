<?php

namespace App\Http\V_1_0_0\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class Order_addons extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function getOrderAddOn($order_item_id)
    {
        return Order_addons::select('id', 'addon_category_id', 'addon_category_name_en', 'addon_category_name_ar', 'addon_category_sort_order','addon_id', 'addon_name_en', 'addon_name_ar', 'price', 'sort_order')->where('order_item_id',$order_item_id)->orderby('id','asc')->get();
    }

}
