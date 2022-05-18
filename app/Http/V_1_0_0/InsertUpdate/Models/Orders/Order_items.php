<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Order_items extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function Insert_orderItems($input, $menuItemDetails)
    {
        $orders = new Order_items;
        $orders->order_id = $input['order_id'];
        $orders->akeed_product_id = $input['akeed_product_id'];
        $orders->akeed_order_item_id = $input['akeed_order_item_id'];
        $orders->customer_id = $input['customer_id'];
        $orders->customer_authentication_id = $input['customer_authentication_id'];
        $orders->vendor_id = $input['vendor_id'];
        $orders->vendor_authentication_id = $input['vendor_authentication_id'];
        $orders->item_name_en = $input['item_name_en'];
        $orders->quantity = $input['quantity'];
        $orders->price = $input['price'];
        $orders->discounted_price = $input['price'];
        $orders->addon_subtotal = $input['addonsubtotal'];
        $orders->subtotal = $input['subtotal'];
        $orders->addon_names = rtrim($input['addon_names'], ',');
        $orders->type = $input['type'];
        if (!empty($menuItemDetails)) {
            $orders->menu_item_id = $menuItemDetails->menuId;
            $orders->menu_item_master_id = $menuItemDetails->menu_item_master_id;
            $orders->item_name_en = $menuItemDetails->item_name_en;
            $orders->item_name_ar = $menuItemDetails->item_name_ar;
            $orders->item_name_slug = $menuItemDetails->item_name_slug;
            $orders->description_en = $menuItemDetails->description_en;
            $orders->description_ar = $menuItemDetails->description_ar;
            $orders->sort_order = $menuItemDetails->sort_order;
            $orders->discount_type = $menuItemDetails->discount_type;
            $orders->discount_percentage = $menuItemDetails->discount_percentage;
            $orders->discount_amount = $menuItemDetails->discount_amount;
            $orders->primary_tag = $menuItemDetails->primary_tag;
            $orders->primary_tag_name = $menuItemDetails->primary_tag_name;
            $orders->secondary_tags = $menuItemDetails->secondary_tags;
            $orders->secondary_tag_names = $menuItemDetails->secondary_tag_names;
            $orders->good_before = $menuItemDetails->good_before;
            $orders->is_addon = $menuItemDetails->is_addon;
        }
        $orders->created_by = 0;
        $orders->save();
        return $orders->id;
    }
}
