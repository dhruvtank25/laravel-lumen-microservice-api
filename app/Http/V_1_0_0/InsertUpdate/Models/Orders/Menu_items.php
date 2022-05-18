<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Menu_items extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public static function getMenuItemDetail($akeed_product_id)
    {
        $menuList = Menu_items::select('id as menuId',  'menu_item_master_id', 'item_name_en', 'item_name_slug', 'item_name_ar','description_en','description_ar', 'sort_order', 'price','discount_type','discount_percentage','discount_amount','type','primary_tag','primary_tag_name','secondary_tags','secondary_tag_names','good_before','is_addon')->where('akeed_menu_item_id',$akeed_product_id);

        return $menuList->first();
    }

}
