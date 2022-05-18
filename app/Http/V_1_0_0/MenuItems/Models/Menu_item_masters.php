<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu_item_masters extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_en', 'name_ar', 'status', 'created_by', 'updated_by'
    ];
    /* To insert the menu item master */
    public static function Insert_menu_item_master($input)
    {
        /* To check the menu item name already exist or not */
        $menu_item_master_exist = Menu_item_masters::where('name_slug','=',Str::slug($input['item_name_en']))->first();
        if (empty($menu_item_master_exist)) {
            $menu_item_master = new Menu_item_masters;
            $menu_item_master->name_slug = Str::slug($input['item_name_en']);
            $menu_item_master->name_en = $input['item_name_en'];
            $menu_item_master->name_ar = isset($input['item_name_ar'])?$input['item_name_ar']:'';
            $menu_item_master->created_by = $input['authentication_id'];
            $menu_item_master->save();
            return $menu_item_master->id;
        } else {
            return $menu_item_master_exist->id;
        }
    }
}
