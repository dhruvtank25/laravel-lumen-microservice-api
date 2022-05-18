<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models;

use Illuminate\Database\Eloquent\Model;

class Menu_masters extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendor_id', 'name_en', 'name_ar', 'description_en', 'description_ar'
    ];

    /* To insert the menu masters */
    public static function Insert_menu($input)
    {
        $menu = new Menu_masters;
        $menu->vendor_id = $input['vendor_id'];
        $menu->save();
        return $menu->id;
    }

    public static function Update_menu($input)
    {
        $menu = Menu_masters::where('vendor_id', "=", $input['vendor_id'])->first();
        if (!empty($menu)) {
            $menu->name_en = isset($input['menu_master_name_en'])?$input['menu_master_name_en']:'';
            $menu->name_ar = isset($input['menu_master_name_ar'])?$input['menu_master_name_ar']:'';
            $menu->description_en = isset($input['menu_master_description_en'])?$input['menu_master_description_en']:'';
            $menu->description_ar = isset($input['menu_master_description_ar'])?$input['menu_master_description_ar']:'';
            $menu->vendor_id = $input['vendor_id'];
            $menu->save();
        } elseif ($input['vendor_id'] <> 0) {
            $menu = new Menu_masters;
            $menu->vendor_id = $input['vendor_id'];
            $menu->save();
        }
        return $menu->first();
    }
}
