<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\V_1_0_0\MenuItems\Models\Addon_categories;
use DB;
class Addon_menu_item_mappings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_id', 'addon_category_id', 'addon_name_en', 'addon_name_ar', 'price', 'status', 'sort_order', 'created_by', 'updated_by'
    ];
    /* To update the add-on menu item mappings */
    public static function updateAddOnMenuItemMapping($data)
    {
        if ($data['is_addon'] == 1 && isset($data['addon_category']) && !empty($data['addon_category'])) {
            /* to check - addd on category is already exist or not. If exist remove that add-on category */
            Addon_menu_item_mappings::where('menu_id', $data['id'])->delete();
            foreach ($data['addon_category'] as $k => $v) {
                foreach ($data['addon_name'][$k] as $key => $val) {
                    $addOnMenuItem = new Addon_menu_item_mappings;
                    $addOnMenuItem->menu_id = $data['id'];
                    $addOnMenuItem->addon_category_id = $v;
                    $addOnMenuItem->addon_category_sort_order = $data['addon_category_sort_order'][$k];
                    $addOnMenuItem->addon_name_en = $val;
                    $addOnMenuItem->addon_name_ar = $data['arabic_addon_name'][$k][$key];
                    $addOnMenuItem->price = $data['addon_price'][$k][$key];
                    $addOnMenuItem->sort_order = $data['addon_sort_order'][$k][$key];
                    $addOnMenuItem->status = isset($data['addon_status'][$k][$key])?$data['addon_status'][$k][$key]:'0';
                    $addOnMenuItem->created_by = $data['authentication_id'];
                    $addOnMenuItem->save();
                }
            }
        }
        return true;
    }
    /* To get the add-on category list using menu id */
    public static function addOnCategoryList($menuId)
    {
        $categoryList = Addon_menu_item_mappings::select('addon_category_id','addon_category_sort_order')->where('menu_id',$menuId)->groupBy('addon_category_id','addon_category_sort_order')->orderBy('addon_category_id','asc')->get();
        return $categoryList;
    }
    /* To get the category details */
    public static function addOnCategoryDetail($menuId, $category_id)
    {
        $categoryDetail = Addon_menu_item_mappings::select('id', 'addon_name_en', 'addon_name_ar', 'price', 'sort_order','status')->where('menu_id',$menuId)->where('addon_category_id',$category_id)->orderBy('sort_order','asc')->get();
        return $categoryDetail;
    }

    public static function insert_excel_addon($input)
    {
        $addOnMenuItem = new Addon_menu_item_mappings;
        $addOnMenuItem->menu_id = $input['menu_id'];
        $addOnMenuItem->addon_category_id = $input['addon_category_id'];
        $addOnMenuItem->addon_category_sort_order =  $input['addon_category_sort'];
        $addOnMenuItem->addon_name_en =  $input['addon_name_en'];
        $addOnMenuItem->addon_name_ar = $input['addon_name_ar'];
        $addOnMenuItem->price =  $input['addon_price'];
        $addOnMenuItem->sort_order = $input['addon_sort'];
        $addOnMenuItem->status = $input['addon_status'];
        $addOnMenuItem->created_by = $input['authentication_id'];
        $addOnMenuItem->save();
        return $input['menu_id'];
    }

    public static function update_excel_addon($input)
    {
        return $input['menu_id'];
    }

    public static function chkAddonExists($input)
    {
        $addon_categories = Addon_categories::select('id')->where('name_en',$input['addon_category_name_en'])->where('status','1')->first();
        if(!empty($addon_categories)){ 
            $addon = Addon_menu_item_mappings::select('id')
                                      ->where('addon_name_en',$input['addon_name_en'])
                                      ->where('addon_category_id',$addon_categories->id)
                                      ->where('menu_id',$input['menu_id'])
                                      ->get();                                                           
            $value = 1;                          
            if(count($addon) > 0){
                $value = 0;
            } 
            return $value;                                     
        } else {
            return 0;
        }
    }

    public static function chkAddonExistAlready($input)
    {
       
        $addon_categories = Addon_categories::select('id')->where('name_en',$input['addon_category_name_en'])->where('status','1')->first();
        if(!empty($addon_categories)){ 
            $addon = Addon_menu_item_mappings::select('id')
                                      ->where('addon_name_en',$input['addon_name_en'])
                                      ->where('addon_category_id',$addon_categories->id)
                                      ->where('menu_id',$input['menu_id'])
                                      ->get();                                                        
            $value = 1;                          
            if(count($addon) > 0){
                $value = 0;
            } 
            return $value;                                     
        } else {
            return 0;
        }
    }
    

    public static function getMenuAddons($menu_id)
    {
        $addons = DB::table('addon_menu_item_mappings as mm')
                      ->select('ac.id as addon_category_id','ac.name_en','ac.name_ar','ac.type as ac_type','ac.mandatory','ac.no_of_mandatory','mm.addon_category_sort_order as ac_sort','ac.status as ac_status',
                      'mm.id as addon_id','mm.addon_name_en','mm.addon_name_ar','mm.price as mm_price','mm.sort_order as mm_sort','mm.status as mm_status')
                      ->leftjoin('addon_categories as ac','mm.addon_category_id','ac.id')
                      ->where('mm.menu_id',$menu_id)
                      ->get();
        return $addons;                      
    }
}
