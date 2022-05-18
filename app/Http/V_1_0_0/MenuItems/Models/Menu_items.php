<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\V_1_0_0\MenuItems\Models\Addon_menu_item_mappings;
use Illuminate\Support\Str;
use DB;

class Menu_items extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendor_id', 'menu_item_master_id', 'item_name_en', 'item_name_slug', 'item_name_ar', 'description_en', 'description_ar', 'price', 'discount_type', 'discount_percentage', 'discount_amount', 'type', 'tags', 'primary_tag', 'secondary_tags', 'good_before', 'available_from_time1', 'available_to_time1', 'available_from_time2', 'available_to_time2', 'available_from_time3', 'available_to_time3', 'available_from_time4', 'available_to_time4', 'full_day', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'full_week', 'image1', 'image2', 'image3', 'image4', 'status', 'is_addon', 'bar_code', 'sku_code', 'created_by', 'updated_by', 'akeed_menu_item_id'
    ];

    /* To get the menu items list */
    public static function getMenuItemList($vendor_id,$key,$offset,$limit)
    {
        $menuItems = Menu_items::select('menu_items.id as menu_item_id',  'menu_items.item_name_en', 'menu_items.price', 'menu_items.type', 'menu_items.is_addon', 'menu_items.status', 'menu_items.image1', 'discount_percentage', 'primary_tag_name', 'menu_items.created_at')
            ->where('menu_items.vendor_id', '=', $vendor_id)->where('menu_items.is_deleted', '=', '0')->orderby('menu_items.id', 'desc');

        if ($key != "") {
            $menuItems->where(function ($query) use ($key) {
                $query->orWhere('menu_items.id', 'LIKE', '%' . $key . '%')->orWhere('menu_items.item_name_en', 'LIKE', '%' . $key . '%')->orWhere('primary_tag_name', 'LIKE', '%' . $key . '%')->orWhere('menu_items.price', 'LIKE', '%' . $key . '%')->orWhere('menu_items.type', 'LIKE', '%' . $key . '%')->orWhere('menu_items.discount_percentage', 'LIKE', '%' . $key . '%')->orWhere('menu_items.discount_amount', 'LIKE', '%' . $key . '%');
            });
        }

        $menuItems=$menuItems->skip($offset)->take($limit);

        return $menuItems->get();
    }

    public static function menuItemCount($vendor_id)
    {
        return Menu_items::select('id')->where('menu_items.vendor_id', '=', $vendor_id)->where('menu_items.is_deleted', '=', '0')->count();

    }

    public static function menuItemFilterCount($vendor_id,$key)
    {
        $menuItems = Menu_items::select('id')->where('menu_items.vendor_id', '=', $vendor_id)->where('menu_items.is_deleted', '=', '0');

        if ($key != "") {
            $menuItems->where(function ($query) use ($key) {
                $query->orWhere('menu_items.id', 'LIKE', '%' . $key . '%')->orWhere('menu_items.item_name_en', 'LIKE', '%' . $key . '%')->orWhere('primary_tag_name', 'LIKE', '%' . $key . '%')->orWhere('menu_items.price', 'LIKE', '%' . $key . '%')->orWhere('menu_items.type', 'LIKE', '%' . $key . '%')->orWhere('menu_items.discount_percentage', 'LIKE', '%' . $key . '%')->orWhere('menu_items.discount_amount', 'LIKE', '%' . $key . '%');
            });
        }
        return $menuItems->count();
    }

    /* To check the name already exist or not */
    public static function checkNameExist($slug_name, $vendor_id, $id = '')
    {
        $checkItemName = Menu_items::select('id')->where('item_name_slug', '=', $slug_name)->where('vendor_id', $vendor_id)->where('is_deleted', '=','0');
        if (!empty($id)) {
            $checkItemName = $checkItemName->where('id', '<>', $id);
        }
        return $checkItemName->first();
    }

    /* To insert the new items */
    public static function Insert_menu_item($input)
    {
        $menu_item = new Menu_items;
        $menu_item->menu_item_master_id = $input['menu_item_master_id'];
        $menu_item->vendor_id = $input['vendor_id'];
        $menu_item->item_name_en = $input['item_name_en'];
        $menu_item->item_name_slug = Str::slug($input['item_name_en']);
        $menu_item->item_name_ar = $input['item_name_ar'];
        $menu_item->description_en = $input['description_en'];
        $menu_item->description_ar = $input['description_ar'];
        $menu_item->price = $input['price'];
        $menu_item->discount_type = $input['discount_type'];
        $menu_item->discount_amount = $menu_item->discount_percentage = 0;
        if ($input['discount_type'] == 'amount') {
            $menu_item->discount_amount = isset($input['discount_amount']) ? $input['discount_amount'] : 0;
        } elseif ($input['discount_type'] == 'percentage') {
            $menu_item->discount_percentage = isset($input['discount_percentage']) ? $input['discount_percentage'] : 0;
        }
        $menu_item->type = $input['type'];
        $menu_item->sort_order = $input['sort_order'];
        $menu_item->primary_tag = $input['primary_tag'];
        $menu_item->primary_tag_name = $input['primary_tag_name'];
        $menu_item->secondary_tags = $input['secondary_tags'];
        $menu_item->secondary_tag_names = $input['secondary_tag_names'];
        $menu_item->good_before = $input['good_before'];
        $menu_item->full_day = strval($input['full_day']);
        $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
        $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
        $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
        if ($input['banner_1'] != "") {
            $menu_item->image1 = $input['banner_1'];
        }
        if ($input['banner_2'] != "") {
            $menu_item->image2 = $input['banner_2'];
        }
        if ($input['banner_3'] != "") {
            $menu_item->image3 = $input['banner_3'];
        }
        if ($input['banner_4'] != "") {
            $menu_item->image4 = $input['banner_4'];
        }
        if ($input['full_day'] == '0') {
            $menu_item->available_from_time1 = ($input['available_from_time1'] != '') ? $input['available_from_time1'] : NULL;
            $menu_item->available_to_time1 = ($input['available_to_time1'] != '') ? $input['available_to_time1'] : NULL;
            $menu_item->available_from_time2 = ($input['available_from_time2'] != '') ? $input['available_from_time2'] : NULL;
            $menu_item->available_to_time2 = ($input['available_to_time2'] != '') ? $input['available_to_time2'] : NULL;
            $menu_item->available_from_time3 = ($input['available_from_time3'] != '') ? $input['available_from_time3'] : NULL;
            $menu_item->available_to_time3 = ($input['available_to_time3'] != '') ? $input['available_to_time3'] : NULL;
            $menu_item->available_from_time4 = ($input['available_from_time4'] != '') ? $input['available_from_time4'] : NULL;
            $menu_item->available_to_time4 = ($input['available_to_time4'] != '') ? $input['available_to_time4'] : NULL;
        }
        $menu_item->full_week = strval($input['full_week']);
        $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
        if ($input['full_week'] == '1') {
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
        } else {
            if (isset($input['days'])) {
                foreach ($input['days'] as $key => $val) {
                    $menu_item->$key = '1';
                }
            }
        }
        $menu_item->status = $input['status'];
        $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
        $menu_item->bar_code = $input['bar_code'];
        $menu_item->sku_code = $input['sku_code'];
        $menu_item->created_by = $input['authentication_id'];
        $menu_item->save();
        return $menu_item->id;
    }

    /* To get the menu item datails */
    public static function getMenuItemDetails($id)
    {
        $menuItems = Menu_items::select('menu_items.id as menu_item_id', 'menu_items.vendor_id', 'menu_items.item_name_en', 'menu_items.item_name_ar', 'menu_items.description_en', 'menu_items.description_ar', 'menu_items.price', 'menu_items.discount_type', 'menu_items.discount_amount', 'menu_items.discount_percentage', 'menu_items.type', 'menu_items.tags', 'menu_items.primary_tag', 'menu_items.secondary_tags', 'menu_items.good_before', 'menu_items.available_from_time1', 'menu_items.available_to_time1', 'menu_items.available_from_time2', 'menu_items.available_to_time2', 'menu_items.available_from_time3', 'menu_items.available_to_time3', 'menu_items.available_from_time4', 'menu_items.available_to_time4', 'menu_items.full_day', 'menu_items.sunday', 'menu_items.monday', 'menu_items.tuesday', 'menu_items.wednesday', 'menu_items.thursday', 'menu_items.friday', 'menu_items.saturday', 'menu_items.full_week', 'menu_items.status', 'menu_items.is_addon', 'menu_items.bar_code', 'menu_items.sku_code', 'menu_items.is_addon', 'menu_items.image1', 'menu_items.image2', 'menu_items.image3', 'menu_items.image4', 'menu_items.aisle_index', 'menu_items.shelf_index', 'menu_items.brand_index', 'menu_items.akeed_menu_item_id','menu_items.sort_order')
            ->where('menu_items.id', $id)->first();
        return $menuItems;
    }

    /* To update the menu items */
    public static function Update_menu_item($input)
    {
        $menu_item = Menu_items::find($input['id']);
        $menu_item->menu_item_master_id = $input['menu_item_master_id'];
        $menu_item->vendor_id = $input['vendor_id'];
        $menu_item->item_name_en = $input['item_name_en'];
        $menu_item->item_name_slug = Str::slug($input['item_name_en']);
        $menu_item->item_name_ar = $input['item_name_ar'];
        $menu_item->description_en = $input['description_en'];
        $menu_item->description_ar = $input['description_ar'];
        $menu_item->price = $input['price'];
        $menu_item->discount_type = $input['discount_type'];
        $menu_item->discount_amount = $menu_item->discount_percentage = 0;
        if ($input['discount_type'] == 'amount') {
            $menu_item->discount_amount = isset($input['discount_amount']) ? $input['discount_amount'] : 0;
        } elseif ($input['discount_type'] == 'percentage') {
            $menu_item->discount_percentage = isset($input['discount_percentage']) ? $input['discount_percentage'] : 0;
        }
        $menu_item->type = $input['type'];
        $menu_item->sort_order = $input['sort_order'];
        $menu_item->primary_tag = $input['primary_tag'];
        $menu_item->primary_tag_name = $input['primary_tag_name'];
        $menu_item->secondary_tags = $input['secondary_tags'];
        $menu_item->secondary_tag_names = $input['secondary_tag_names'];
        $menu_item->good_before = $input['good_before'];
        $menu_item->full_day = strval($input['full_day']);
        $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
        $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
        $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
        if ($input['banner_1'] != "") {
            $menu_item->image1 = $input['banner_1'];
        }
        if ($input['banner_2'] != "") {
            $menu_item->image2 = $input['banner_2'];
        }
        if ($input['banner_3'] != "") {
            $menu_item->image3 = $input['banner_3'];
        }
        if ($input['banner_4'] != "") {
            $menu_item->image4 = $input['banner_4'];
        }
        if ($input['full_day'] == '0') {
            $menu_item->available_from_time1 = ($input['available_from_time1'] != '') ? $input['available_from_time1'] : NULL;
            $menu_item->available_to_time1 = ($input['available_to_time1'] != '') ? $input['available_to_time1'] : NULL;
            $menu_item->available_from_time2 = ($input['available_from_time2'] != '') ? $input['available_from_time2'] : NULL;
            $menu_item->available_to_time2 = ($input['available_to_time2'] != '') ? $input['available_to_time2'] : NULL;
            $menu_item->available_from_time3 = ($input['available_from_time3'] != '') ? $input['available_from_time3'] : NULL;
            $menu_item->available_to_time3 = ($input['available_to_time3'] != '') ? $input['available_to_time3'] : NULL;
            $menu_item->available_from_time4 = ($input['available_from_time4'] != '') ? $input['available_from_time4'] : NULL;
            $menu_item->available_to_time4 = ($input['available_to_time4'] != '') ? $input['available_to_time4'] : NULL;
        }
        $menu_item->full_week = strval($input['full_week']);
        $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
        if ($input['full_week'] == '1') {
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
        } else {
            if (isset($input['days'])) {
                foreach ($input['days'] as $key => $val) {
                    $menu_item->$key = '1';
                }
            }
        }
        $menu_item->status = $input['status'];
        $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
        $menu_item->bar_code = $input['bar_code'];
        $menu_item->sku_code = $input['sku_code'];
        $menu_item->updated_by = $input['authentication_id'];
        $menu_item->save();
        return $menu_item->id;
    }

    /* To update the menu item */
    public static function updateStatus($id, $status)
    {
        $update_menu_item = Menu_items::find($id);
        $update_menu_item->status = $status;
        $update_menu_item->save();
        return $update_menu_item->first();
    }
    /* To get the vendor primary tag list */
    public static function primaryTagList($vendorId)
    {
        $primaryTags = Menu_items::select(DB::raw('GROUP_CONCAT(primary_tag) as primary_tags'))->where('vendor_id', $vendorId)
            ->groupby('id');
        return $primaryTags->first();
    }

    /* To update the menu item - akeed id */
    public static function updateAkeedMenuId($input)
    {
        $update_menu_item = Menu_items::find($input['id']);
        if (!empty($update_menu_item)) {
            $update_menu_item->akeed_menu_item_id = $input['akeed_menu_item_id'];
            $update_menu_item->save();
            return $update_menu_item;
        }
        return 0;
    }

    public static function getVendorMenuItems($input)
    {
        $menu_items = Menu_items::select('menu_items.id','menu_items.item_name_en','menu_items.item_name_ar','menu_items.description_en',
                                'menu_items.description_ar','menu_items.sort_order','menu_items.price','menu_items.discount_type',
                                'menu_items.discount_percentage','menu_items.discount_amount','menu_items.type','menu_items.tags','menu_items.primary_tag',
                                'menu_items.primary_tag_name','menu_items.secondary_tags','menu_items.secondary_tag_names','menu_items.good_before',
                                'menu_items.available_from_time1','menu_items.available_to_time1','menu_items.available_from_time2','menu_items.available_to_time2',
                                'menu_items.available_from_time3','menu_items.available_to_time3','menu_items.available_from_time4','menu_items.available_to_time4',
                                'menu_items.full_day','menu_items.sunday','menu_items.monday','menu_items.tuesday','menu_items.wednesday','menu_items.thursday',
                                'menu_items.friday','menu_items.saturday','menu_items.full_week','menu_items.is_addon','menu_items.status','menu_items.bar_code','menu_items.sku_code',
                                'menu_items.aisle_index','menu_items.shelf_index','menu_items.brand_index','menu_items.image1','menu_items.image2','menu_items.image3','menu_items.image4', 'menu_items.vendor_id',  
                                'ac.id as addon_category_id','ac.name_en','ac.name_ar','ac.type as ac_type','ac.mandatory','ac.no_of_mandatory','mm.addon_category_sort_order as ac_sort','ac.status as ac_status',
                                'mm.id as addon_id','mm.addon_name_en','mm.addon_name_ar','mm.price as mm_price','mm.sort_order as mm_sort','mm.status as mm_status')
                                ->leftjoin('addon_menu_item_mappings as mm','menu_items.id','mm.menu_id')
                                ->leftjoin('addon_categories as ac','mm.addon_category_id','ac.id')
                                ->where('menu_items.vendor_id',$input['vendor_id'])
                                ->where('menu_items.is_deleted','0')
                                ->orderby('menu_items.id','asc')
                                ->get();
        return $menu_items;                                    
    }

    public static function getVendorMenu($input)
    {
        $menu_items = Menu_items::select('menu_items.id','menu_items.item_name_en','menu_items.item_name_ar','menu_items.description_en',
                                'menu_items.description_ar','menu_items.sort_order','menu_items.price','menu_items.discount_type',
                                'menu_items.discount_percentage','menu_items.discount_amount','menu_items.type','menu_items.tags','menu_items.primary_tag',
                                'menu_items.primary_tag_name','menu_items.secondary_tags','menu_items.secondary_tag_names','menu_items.good_before',
                                'menu_items.available_from_time1','menu_items.available_to_time1','menu_items.available_from_time2','menu_items.available_to_time2',
                                'menu_items.available_from_time3','menu_items.available_to_time3','menu_items.available_from_time4','menu_items.available_to_time4',
                                'menu_items.full_day','menu_items.sunday','menu_items.monday','menu_items.tuesday','menu_items.wednesday','menu_items.thursday',
                                'menu_items.friday','menu_items.saturday','menu_items.full_week','menu_items.is_addon','menu_items.status','menu_items.bar_code','menu_items.sku_code',
                                'menu_items.aisle_index','menu_items.shelf_index','menu_items.brand_index','menu_items.image1','menu_items.image2','menu_items.image3','menu_items.image4','menu_items.vendor_id')
                                ->where('menu_items.vendor_id',$input['vendor_id'])
                                ->where('menu_items.is_deleted','0')
                                ->orderby('menu_items.id','asc')
                                ->get();
        return $menu_items;                                    
    }

    public static function insert_excel_menu($input)
    {
        $menu_item = new Menu_items;
        $menu_item->menu_item_master_id = $input['menu_item_master_id'];
        $menu_item->vendor_id = $input['vendor_id'];
        $menu_item->item_name_en = $input['item_name_en'];
        $menu_item->item_name_slug = Str::slug($input['item_name_en']);
        $menu_item->item_name_ar = $input['item_name_ar'];
        $menu_item->description_en = $input['description_en'];
        $menu_item->description_ar = $input['description_ar'];
        $menu_item->price = $input['item_price'];
        if($input['discount_type'] != ''){
            $menu_item->discount_type = $input['discount_type'];
        }
        $menu_item->discount_amount = $menu_item->discount_percentage = 0;
        if ($input['discount_type'] == 'amount') {
            $menu_item->discount_amount = $input['discount_amount'];
        } elseif ($input['discount_type'] == 'percentage') {
            $menu_item->discount_percentage = $input['discount_percentage'];
        }
        $menu_item->type = $input['type'];
        $menu_item->sort_order = $input['sort'];
        $menu_item->primary_tag = $input['primary_tag'];
        $menu_item->primary_tag_name = $input['primary_tag_name'];
        $menu_item->secondary_tags = $input['secondary_tags'];
        $menu_item->secondary_tag_names = $input['secondary_tag_names'];
        $menu_item->good_before = $input['good_before'];
        $menu_item->full_day = strval($input['full_day']);
        $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
        $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
        $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
        if ($input['full_day'] == '0') {
            $menu_item->available_from_time1 = ($input['from_time1'] != '') ? date("H:i", strtotime($input['from_time1'])) : NULL;
            $menu_item->available_to_time1 = ($input['to_time1'] != '') ? date("H:i", strtotime($input['to_time1'])) : NULL;
            $menu_item->available_from_time2 = ($input['from_time2'] != '') ? date("H:i", strtotime($input['from_time2'])) : NULL;
            $menu_item->available_to_time2 = ($input['to_time2'] != '') ?  date("H:i", strtotime($input['to_time2'])) : NULL;
            $menu_item->available_from_time3 = ($input['from_time3'] != '') ? date("H:i", strtotime($input['from_time3'])) : NULL;
            $menu_item->available_to_time3 = ($input['to_time3'] != '') ?  date("H:i", strtotime($input['to_time3'])) : NULL;
            $menu_item->available_from_time4 = ($input['from_time4'] != '') ?date("H:i", strtotime($input['from_time4'])) : NULL;
            $menu_item->available_to_time4 = ($input['to_time4'] != '') ?  date("H:i", strtotime($input['to_time4'])) : NULL;
        }
        $menu_item->full_week = strval($input['full_week']);
        $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
        if ($input['full_week'] == '1') {
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
        } else {
            $menu_item->sunday = $input['sunday'];
            $menu_item->monday = $input['monday'];
            $menu_item->tuesday = $input['tuesday'];
            $menu_item->wednesday = $input['wednesday'];
            $menu_item->thursday = $input['thursday'];
            $menu_item->friday = $input['friday'];
            $menu_item->saturday = $input['saturday'];
        }
        $menu_item->status = $input['status'];
        $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
        $menu_item->bar_code = $input['bar_code'];
        $menu_item->sku_code = $input['sku_code'];
        $menu_item->created_by = $input['authentication_id'];
        $menu_item->save();
        return $menu_item->id;
    }

    public static function update_excel_menu($input)
    {
        $menu_item = Menu_items::find($input['menu_id']);
        if(!empty($menu_item) > 0){
            //$menu_item->menu_item_master_id = $input['menu_item_master_id'];
            //$menu_item->vendor_id = $input['vendor_id'];
            $menu_item->item_name_en = $input['item_name_en'];
            $menu_item->item_name_slug = Str::slug($input['item_name_en']);
            $menu_item->item_name_ar = $input['item_name_ar'];
            $menu_item->description_en = $input['description_en'];
            $menu_item->description_ar = $input['description_ar'];
            $menu_item->price = $input['item_price'];
            if($input['discount_type'] != ''){
                $menu_item->discount_type = $input['discount_type'];
            }
            $menu_item->discount_amount = $menu_item->discount_percentage = 0;
            if ($input['discount_type'] == 'amount') {
                $menu_item->discount_amount = $input['discount_amount'];
            } elseif ($input['discount_type'] == 'percentage') {
                $menu_item->discount_percentage = $input['discount_percentage'];
            }
            $menu_item->type = $input['type'];
            $menu_item->sort_order = $input['sort'];
            $menu_item->primary_tag = $input['primary_tag'];
            $menu_item->primary_tag_name = $input['primary_tag_name'];
            $menu_item->secondary_tags = $input['secondary_tags'];
            $menu_item->secondary_tag_names = $input['secondary_tag_names'];
            $menu_item->good_before = $input['good_before'];
            $menu_item->full_day = strval($input['full_day']);
            $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
            $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
            $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
            $menu_item->image1 = NULL;
            $menu_item->image2 = NULL;
            $menu_item->image3 = NULL;
            $menu_item->image4 = NULL;
            if ($input['full_day'] == '0') {
                $menu_item->available_from_time1 = ($input['from_time1'] != '') ? date("H:i", strtotime($input['from_time1'])) : NULL;
                $menu_item->available_to_time1 = ($input['to_time1'] != '') ? date("H:i", strtotime($input['to_time1'])) : NULL;
                $menu_item->available_from_time2 = ($input['from_time2'] != '') ? date("H:i", strtotime($input['from_time2'])) : NULL;
                $menu_item->available_to_time2 = ($input['to_time2'] != '') ?  date("H:i", strtotime($input['to_time2'])) : NULL;
                $menu_item->available_from_time3 = ($input['from_time3'] != '') ? date("H:i", strtotime($input['from_time3'])) : NULL;
                $menu_item->available_to_time3 = ($input['to_time3'] != '') ?  date("H:i", strtotime($input['to_time3'])) : NULL;
                $menu_item->available_from_time4 = ($input['from_time4'] != '') ?date("H:i", strtotime($input['from_time4'])) : NULL;
                $menu_item->available_to_time4 = ($input['to_time4'] != '') ?  date("H:i", strtotime($input['to_time4'])) : NULL;
            }
            $menu_item->full_week = strval($input['full_week']);
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
            if ($input['full_week'] == '1') {
                $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
            } else {
                $menu_item->sunday = $input['sunday'];
                $menu_item->monday = $input['monday'];
                $menu_item->tuesday = $input['tuesday'];
                $menu_item->wednesday = $input['wednesday'];
                $menu_item->thursday = $input['thursday'];
                $menu_item->friday = $input['friday'];
                $menu_item->saturday = $input['saturday'];
            }
            $menu_item->status = $input['status'];
            $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
            $menu_item->bar_code = $input['bar_code'];
            $menu_item->sku_code = $input['sku_code'];
            $menu_item->updated_by = $input['authentication_id'];
            $menu_item->save();
            
            $del_addon = Addon_menu_item_mappings::where('menu_id',$input['menu_id'])->delete();
            return $menu_item->id;  
        }
    }

    public static function chkMenuExists($input)
    {
        $menu_item = Menu_items::where('item_name_en',$input['item_name_en'])->first();
        if(!empty($menu_item) > 0){
            $menu_item->item_name_en = $input['item_name_en'];
            $menu_item->item_name_slug = Str::slug($input['item_name_en']);
            $menu_item->item_name_ar = $input['item_name_ar'];
            $menu_item->description_en = $input['description_en'];
            $menu_item->description_ar = $input['description_ar'];
            $menu_item->price = $input['item_price'];
            if($input['discount_type'] != ''){
                $menu_item->discount_type = $input['discount_type'];
            }
            $menu_item->discount_amount = $menu_item->discount_percentage = 0;
            if ($input['discount_type'] == 'amount') {
                $menu_item->discount_amount = $input['discount_amount'];
            } elseif ($input['discount_type'] == 'percentage') {
                $menu_item->discount_percentage = $input['discount_percentage'];
            }
            $menu_item->type = $input['type'];
            $menu_item->sort_order = $input['sort'];
            $menu_item->primary_tag = $input['primary_tag'];
            $menu_item->primary_tag_name = $input['primary_tag_name'];
            $menu_item->secondary_tags = $input['secondary_tags'];
            $menu_item->secondary_tag_names = $input['secondary_tag_names'];
            $menu_item->good_before = $input['good_before'];
            $menu_item->full_day = strval($input['full_day']);
            $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
            $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
            $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
            $menu_item->image1 = NULL;
            $menu_item->image2 = NULL;
            $menu_item->image3 = NULL;
            $menu_item->image4 = NULL;
            if ($input['full_day'] == '0') {
                $menu_item->available_from_time1 = ($input['from_time1'] != '') ? date("H:i", strtotime($input['from_time1'])) : NULL;
                $menu_item->available_to_time1 = ($input['to_time1'] != '') ? date("H:i", strtotime($input['to_time1'])) : NULL;
                $menu_item->available_from_time2 = ($input['from_time2'] != '') ? date("H:i", strtotime($input['from_time2'])) : NULL;
                $menu_item->available_to_time2 = ($input['to_time2'] != '') ?  date("H:i", strtotime($input['to_time2'])) : NULL;
                $menu_item->available_from_time3 = ($input['from_time3'] != '') ? date("H:i", strtotime($input['from_time3'])) : NULL;
                $menu_item->available_to_time3 = ($input['to_time3'] != '') ?  date("H:i", strtotime($input['to_time3'])) : NULL;
                $menu_item->available_from_time4 = ($input['from_time4'] != '') ?date("H:i", strtotime($input['from_time4'])) : NULL;
                $menu_item->available_to_time4 = ($input['to_time4'] != '') ?  date("H:i", strtotime($input['to_time4'])) : NULL;
            }
            $menu_item->full_week = strval($input['full_week']);
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
            if ($input['full_week'] == '1') {
                $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
            } else {
                $menu_item->sunday = $input['sunday'];
                $menu_item->monday = $input['monday'];
                $menu_item->tuesday = $input['tuesday'];
                $menu_item->wednesday = $input['wednesday'];
                $menu_item->thursday = $input['thursday'];
                $menu_item->friday = $input['friday'];
                $menu_item->saturday = $input['saturday'];
            }
            $menu_item->status = $input['status'];
            $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
            $menu_item->bar_code = $input['bar_code'];
            $menu_item->sku_code = $input['sku_code'];
            $menu_item->updated_by = $input['authentication_id'];
            $menu_item->save();
            
            $del_addon = Addon_menu_item_mappings::where('menu_id',$menu_item->id)->delete();
            return $menu_item->id; 
        } else {
            $menu_item = new Menu_items;
            $menu_item->menu_item_master_id = $input['menu_item_master_id'];
            $menu_item->vendor_id = $input['vendor_id'];
            $menu_item->item_name_en = $input['item_name_en'];
            $menu_item->item_name_slug = Str::slug($input['item_name_en']);
            $menu_item->item_name_ar = $input['item_name_ar'];
            $menu_item->description_en = $input['description_en'];
            $menu_item->description_ar = $input['description_ar'];
            $menu_item->price = $input['item_price'];
            if($input['discount_type'] != ''){
                $menu_item->discount_type = $input['discount_type'];
            }
            $menu_item->discount_amount = $menu_item->discount_percentage = 0;
            if ($input['discount_type'] == 'amount') {
                $menu_item->discount_amount = $input['discount_amount'];
            } elseif ($input['discount_type'] == 'percentage') {
                $menu_item->discount_percentage = $input['discount_percentage'];
            }
            $menu_item->type = $input['type'];
            $menu_item->sort_order = $input['sort'];
            $menu_item->primary_tag = $input['primary_tag'];
            $menu_item->primary_tag_name = $input['primary_tag_name'];
            $menu_item->secondary_tags = $input['secondary_tags'];
            $menu_item->secondary_tag_names = $input['secondary_tag_names'];
            $menu_item->good_before = $input['good_before'];
            $menu_item->full_day = strval($input['full_day']);
            $menu_item->aisle_index = ($input['aisle_index'] != '') ? $input['aisle_index'] : '';
            $menu_item->shelf_index = ($input['shelf_index'] != '') ? $input['shelf_index'] : '';
            $menu_item->brand_index = ($input['brand_index'] != '') ? $input['brand_index'] : '';
            if ($input['full_day'] == '0') {
                $menu_item->available_from_time1 = ($input['from_time1'] != '') ? date("H:i", strtotime($input['from_time1'])) : NULL;
                $menu_item->available_to_time1 = ($input['to_time1'] != '') ? date("H:i", strtotime($input['to_time1'])) : NULL;
                $menu_item->available_from_time2 = ($input['from_time2'] != '') ? date("H:i", strtotime($input['from_time2'])) : NULL;
                $menu_item->available_to_time2 = ($input['to_time2'] != '') ?  date("H:i", strtotime($input['to_time2'])) : NULL;
                $menu_item->available_from_time3 = ($input['from_time3'] != '') ? date("H:i", strtotime($input['from_time3'])) : NULL;
                $menu_item->available_to_time3 = ($input['to_time3'] != '') ?  date("H:i", strtotime($input['to_time3'])) : NULL;
                $menu_item->available_from_time4 = ($input['from_time4'] != '') ?date("H:i", strtotime($input['from_time4'])) : NULL;
                $menu_item->available_to_time4 = ($input['to_time4'] != '') ?  date("H:i", strtotime($input['to_time4'])) : NULL;
            }
            $menu_item->full_week = strval($input['full_week']);
            $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '0';
            if ($input['full_week'] == '1') {
                $menu_item->sunday = $menu_item->monday = $menu_item->tuesday = $menu_item->wednesday = $menu_item->thursday = $menu_item->friday = $menu_item->saturday = '1';
            } else {
                $menu_item->sunday = $input['sunday'];
                $menu_item->monday = $input['monday'];
                $menu_item->tuesday = $input['tuesday'];
                $menu_item->wednesday = $input['wednesday'];
                $menu_item->thursday = $input['thursday'];
                $menu_item->friday = $input['friday'];
                $menu_item->saturday = $input['saturday'];
            }
            $menu_item->status = $input['status'];
            $menu_item->is_addon = isset($input['is_addon']) ? $input['is_addon'] : '0';
            $menu_item->bar_code = $input['bar_code'];
            $menu_item->sku_code = $input['sku_code'];
            $menu_item->created_by = $input['authentication_id'];
            $menu_item->save();
            return $menu_item->id;
        }
    }

    public static function updateMenuImage($image,$menu_id)
    {
        Menu_items::where('id',$menu_id)->update($image);
        return true;
    }

    public static function getMenuIdByName($menu_name,$vendor_id)
    {
        $menu = Menu_items::select('id')->where('item_name_en',$menu_name)->where('vendor_id',$vendor_id)->first();
        if(!empty($menu)){
            return $menu->id;
        } else {
            return 0;
        }
    }
    /* To update the menu item */
    public static function updateStatususingAkeedMenuItem($akeed_menu_item_id, $status)
    {
        $update_menu_item = Menu_items::where('akeed_menu_item_id','=',$akeed_menu_item_id)->first();
        if (!empty($update_menu_item)) {
            $update_menu_item->status = $status;
            $update_menu_item->save();
            return true;
        }
        return false;
    }
}
