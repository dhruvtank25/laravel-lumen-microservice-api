<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class Addon_categories extends Model
{
    /* To get the add-on category details */
    public static function getAddOnCategoryDetails($id)
    {
        $addon_category = Addon_categories::find($id);
        return $addon_category;
    }

    public static function getAddonCategories()
    {
        $addon_category = Addon_categories::select('*')->where('status','1')->get();
        $categories = array();
        if(!empty($addon_category)){
            foreach($addon_category as $key => $val){
                $arr = array();
                $arr['name_en'] = $val->name_en;
                $arr['type'] = $val->type;
                $arr['mandatory'] = $val->mandatory;
                $arr['no_of_mandatory'] = $val->no_of_mandatory;
                $categories[] = $arr; 
            }
        }
        return $categories;
    }

    public static function getAddonCategoryId($input)
    {
        $addon_categories = Addon_categories::select('id')->where('name_en',$input['addon_category_name_en'])->where('status','1')->first();
        return $addon_categories->id;
    }
    
    public static function getMenuAddonCategoryId($input)
    {
        $addon_categories = Addon_categories::select('id')->where('name_en',$input['addon_category_name_en'])->where('status','1')->first();
        return $addon_categories->id;
    }

}
