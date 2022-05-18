<?php

namespace App\Http\V_1_0_0\MenuItems\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Http\V_1_0_0\MenuItems\Models\Menu_item_masters;
use App\Http\V_1_0_0\MenuItems\Models\Menu_items;
use App\Http\V_1_0_0\MenuItems\Models\Tag_masters;
use App\Http\V_1_0_0\MenuItems\Models\Addon_menu_item_mappings;
use App\Http\V_1_0_0\MenuItems\Models\Vendors;
use App\Http\V_1_0_0\MenuItems\Models\Addon_categories;
use App\Http\V_1_0_0\MenuItems\Models\Import_error_logs;

use App\Helpers\ResponseBuilder;

//use Cache;

class MenuItemController extends Controller
{
    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * list api
     *
     * @return \Illuminate\Http\Response
     * To show on all the vendors in vendor management
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $vendor_id=$input['vendor_id'];
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'vendor_id' => ['required']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $key = trim($input['q']);
                $offset = $input['page'];
                $limit = $input['per_page'];
                $menu_items_list['menuItems_list'] = Menu_items::getMenuItemList($vendor_id,$key,$offset,$limit);
                $menu_items_list['menuItems_count'] =Menu_items::menuItemCount($vendor_id);
                $menu_items_list['menuItems_filter_count'] =Menu_items::menuItemFilterCount($vendor_id,$key);
                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item List has been fetched successfully', $menu_items_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To add the menu item list */
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'item_name_en' => ['required', 'max:254'],
                'description_en' => ['required'],
                'price' => ['required', 'numeric', 'min:0'],
                'type' => ['in:Veg,Non-Veg', 'required'],
                'sort_order' => ['required', 'numeric'],
                'primary_tag' => ['required'],
                'secondary_tags' => ['required', 'array'],
                //'good_before' => ['required', 'max:32', 'regex:/^[a-zA-Z0-9 ]+$/'],
                'full_day' => ['required', 'boolean'],
                'days' => ['required', 'array'],
                'full_week' => ['required', 'boolean'],
                'available_from_time1' => ['required_if:full_day,0'],
                'available_to_time1' => ['required_if:full_day,0'],
                'status' => ['required', 'boolean'],
                'is_addon' => ['required', 'boolean'],
                'banner_1' => ['required', 'max:2024']
            ];
            if ($input['discount_type'] == 'percentage') {
                $rules['discount_percentage'] = 'required|numeric|between:0.1,99.99';
            }
            if ($input['discount_type'] == 'amount') {
                $rules['discount_amount'] = 'required|numeric|min:1';
            }
            if ($input['bar_code'] != '') {
                $rules['bar_code'] = 'max:50|regex:/^[a-zA-Z0-9]+$/';
            }
            if ($input['sku_code'] != '') {
                $rules['sku_code'] = 'max:50|regex:/^[a-zA-Z0-9]+$/';
            }
            if ($input['aisle_index'] != '') {
                $rules['aisle_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            if ($input['shelf_index'] != '') {
                $rules['shelf_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            if ($input['brand_index'] != '') {
                $rules['brand_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            $validator = app('validator')->make($input, $rules);
            $validator->after(function ($validator) use ($input) {
                if (isset($input['item_name_en']) && !empty($input['item_name_en']) && isset($input['vendor_id']) && !empty($input['vendor_id'])) {
                    $item_name_slug = Str::slug($input['item_name_en']);
                    /* to check the name is already exist or not */
                    $check_name_exist = Menu_items::checkNameExist($item_name_slug, $input['vendor_id']);
                    if (!empty($check_name_exist)) {
                        $validator->errors()->add('item_name_en', 'The Item Name has already been taken.');
                    }
                }
                /* If the discount type is not empty, to check the discount amount */
                if (isset($input['discount_type']) && $input['discount_type'] == 'amount') {
                    if ($input['price'] < $input['discount_amount']) {
                        $validator->errors()->add('discount_amount', 'Discount amount should be lesser than menu item price');
                    }
                }

                /* To check the available time valid or not */
                if (isset($input['full_day']) && $input['full_day'] == 0) {
                    for ($i = 1; $i < 5; $i++) {
                        if (($input['available_from_time' . $i] <> "" || $input['available_to_time' . $i] <> "") && ($input['available_from_time' . $i] == "" || $input['available_to_time' . $i] == "")) {
                            $validator->errors()->add('available_from_time' . $i, 'Both from time and to time is mandatory.');
                        }
                        if (!empty($input['available_from_time' . $i]) && !empty($input['available_to_time' . $i])) {
                            $fromTime1 = strtotime($input['available_from_time' . $i]);
                            $toTime1 = strtotime($input['available_to_time' . $i]);
                            if ($fromTime1 >= $toTime1) {
                                $validator->errors()->add('available_from_time' . $i, 'Available To Time' . $i . ' should be greater than from time.');
                            }
                            for ($j = 1; $j < 5; $j++) {
                                if (($i <> $j) && !empty($input['available_from_time' . $j]) && !empty($input['available_to_time' . $j])) {
                                    $fromTime2 = strtotime($input['available_from_time' . $j]);
                                    $toTime2 = strtotime($input['available_to_time' . $j]);
                                    if (($fromTime1 > $fromTime2 && $fromTime1 <= $toTime2) || ($toTime1 > $fromTime2 && $toTime1 <= $toTime2)) {
                                        $validator->errors()->add('available_from_time' . $i, 'Available Time' . $i . '-Please provide proper from and to timings.');
                                    }
                                }
                            }
                        }
                    }
                }
                /* To check the add-on category name already exist or not */
                if ($input['is_addon'] == 1) {
                    if (isset($input['addon_category']) && count($input['addon_category']) > 0) {
                        $check_category_empty = 0;
                        if (!empty($input['addon_category'])) {
                            $i = 1;
                            foreach ($input['addon_category'] as $addon_key => $addon_val) {
                                if (empty($addon_val)) {
                                    $check_category_empty = 1;
                                    $validator->errors()->add('addon_category[' . $addon_key . ']', 'Add-On Category-' . $i . ' field is required.');
                                }
                                /* To check the add-on sort-order is required or not condition */
                                if (isset($input['addon_category_sort_order'][$addon_key]) && !empty($input['addon_category_sort_order'][$addon_key])) {
                                    $addon_category_sort_order = trim($input['addon_category_sort_order'][$addon_key]);
                                    if (!preg_match("/^[0-9]+$/", $addon_category_sort_order)) {
                                        $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                    }
                                } else {
                                    $validator->errors()->add('addon_category_sort_order[' . $addon_key . ']', 'Add-On Category Sort Order-' . $i . ' field is required.');
                                }
                                /* To get the add-on category details */
                                $addOnCategoryDetail = Addon_categories::getAddOnCategoryDetails($addon_val);
                                if (!empty($addOnCategoryDetail)) {
                                    $no_of_mandatory = $addOnCategoryDetail->no_of_mandatory;
                                    if ($addOnCategoryDetail->mandatory == 1) {
                                        /* To check the add-on name is required or not condition */
                                        if (isset($input['addon_name'][$addon_key]) && count($input['addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_name'][$addon_key] as $name_key => $name_val) {
                                                $name_val = trim($name_val);
                                                if (empty($name_val)) {
                                                    $validator->errors()->add('addon_name[' . $addon_key . '][' . $name_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[a-zA-Z0-9 _\-.,:!@#$%*&()']+$/", $name_val)) {
                                                    //if(preg_match("/^[a-zA-Z0-9 _-.,:"']+$/", $name_val) === 0)
                                                    $validator->errors()->add('addon_name[' . $addon_key . '][' . $name_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                            $addOnName = isset($input['addon_name'][$addon_key]) ? $input['addon_name'][$addon_key] : array();
                                            $addOnStatus = isset($input['addon_status'][$addon_key]) ? $input['addon_status'][$addon_key] : array();
                                            if (count($addOnName) > count(array_unique($addOnName))) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Duplicates occured in add-on names (' . $addOnCategoryDetail->name_en . ')');
                                            } elseif ($no_of_mandatory > count($addOnStatus)) {
                                                $validator->errors()->add('addon_status[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . ' Active.(For ' . $addOnCategoryDetail->name_en . ')');
                                            } elseif ($no_of_mandatory > ($j - 1)) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . '(For ' . $addOnCategoryDetail->name_en . ')');
                                            }
                                        } else {
                                            $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on arabic name is required or not condition */
                                        if (isset($input['arabic_addon_name'][$addon_key]) && count($input['arabic_addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['arabic_addon_name'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-' . $j . ' field is required.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on sort-order is required or not condition */
                                        if (isset($input['addon_sort_order'][$addon_key]) && count($input['addon_sort_order'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_sort_order'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9]+$/", $order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on price is required or not condition */
                                        if (isset($input['addon_price'][$addon_key]) && count($input['addon_price'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_price'][$addon_key] as $price_key => $price_val) {
                                                $price_val = trim($price_val);
                                                if (empty($price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9.]+$/", $price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price -' . $i . '-1 field is required.');
                                        }
                                    } else {//Not mandatory

                                        /* To check the add-on name is required or not condition */
                                        if (isset($input['addon_name'][$addon_key]) && count($input['addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_name'][$addon_key] as $name_key => $name_val) {
                                                $name_val = trim($name_val);
                                                if (empty($name_val)) {
                                                    $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[a-zA-Z0-9 _\-.,:!@#$%*&()']+$/", $name_val)) {
                                                    //if(preg_match("/^[a-zA-Z0-9 _-.,:"']+$/", $name_val) === 0)
                                                    $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                            $addOnName = isset($input['addon_name'][$addon_key]) ? $input['addon_name'][$addon_key] : array();
                                            $addOnStatus = isset($input['addon_status'][$addon_key]) ? $input['addon_status'][$addon_key] : array();
                                            if (count($addOnName) > count(array_unique($addOnName))) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Duplicates occured in add-on names (' . $addOnCategoryDetail->name_en . ')');
                                            }
                                            if ($addOnCategoryDetail->type == 'Exactly' && $no_of_mandatory > count($addOnStatus)) {
                                                $validator->errors()->add('addon_status[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . ' Active.(For ' . $addOnCategoryDetail->name_en . ')');
                                            }
                                        } else {
                                            $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on arabic name is required or not condition */
                                        if (isset($input['arabic_addon_name'][$addon_key]) && count($input['arabic_addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['arabic_addon_name'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-' . $j . ' field is required.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on sort-order is required or not condition */
                                        if (isset($input['addon_sort_order'][$addon_key]) && count($input['addon_sort_order'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_sort_order'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9]+$/", $order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on price is required or not condition */
                                        if (isset($input['addon_price'][$addon_key]) && count($input['addon_price'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_price'][$addon_key] as $price_key => $price_val) {
                                                $price_val = trim($price_val);
                                                if (empty($price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9.]+$/", $price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on status is required or not condition */
                                        if (!isset($input['addon_status'][$addon_key])) {
                                            $validator->errors()->add('addon_status[' . $addon_key . ']', 'You need to activate atleast one Add-On Status (For ' . $addOnCategoryDetail->name_en . ')');
                                        }
                                    }
                                } else {
                                    $validator->errors()->add('addon_category[' . $addon_key . ']', 'Invalid Add-On Category-' . $i);
                                }
                                $i++;
                            }
                        }
                        if ($check_category_empty == 0 && (count($input['addon_category']) <> count(array_unique($input['addon_category'])))) {
                            $validator->errors()->add('addon_category', 'Add-On Category is already existed.');
                        }
                    } else {
                        $validator->errors()->add('addon_category', 'Add-On Category-1 field is required.');
                        $validator->errors()->add('addon_name', 'Add-On Name-1 field is required.');
                        $validator->errors()->add('addon_price', 'Add-On Price-1 field is required.');
                        $validator->errors()->add('addon_sort_order', 'Add-On Sort Order-1 field is required.');
                    }
                }
            });

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }

            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);

            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                /* To insert the menu masters table */
                $menu_item_master_id = Menu_item_masters::Insert_menu_item_master($input);
                $input['menu_item_master_id'] = $menu_item_master_id;
                //$primary_tag = Tag_masters::Insert_tags($input, 1);
                //$input['primary_tag'] = $primary_tag;
                $input['primary_tag_name'] = Tag_masters::Get_primary_tag($input['primary_tag']);
                //$secondary_tags = Tag_masters::Insert_tags($input, 2);
                //$input['secondary_tags'] = $secondary_tags;
				$input['secondary_tags'] = implode(',',$input['secondary_tags']);
                $input['secondary_tag_names'] = Tag_masters::Get_secondary_tags($input['secondary_tags']);
                $menuItemId = Menu_items::Insert_menu_item($input);
                $input['id'] = $menuItemId;
                Addon_menu_item_mappings::updateAddOnMenuItemMapping($input);
                /* To update the vendor primary tags */
                //$primaryTagList = Menu_items::primaryTagList($input['vendor_id']);
                //Vendors::updateVendorPrimaryTags($input['vendor_id'], $primaryTagList);

                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item Saved Successfully');//, $menuItemId
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the menu item list */
    public function update(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:menu_items,id'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'item_name_en' => ['required', 'max:254'],
                'description_en' => ['required'],
                'price' => ['required', 'numeric', 'min:0'],
                'type' => ['in:Veg,Non-Veg', 'required'],
                'sort_order' => ['required', 'numeric'],
                'primary_tag' => ['required'],
                'secondary_tags' => ['required', 'array'],
                //'good_before' => ['required', 'max:32', 'regex:/^[a-zA-Z0-9 ]+$/'],
                'full_day' => ['required', 'boolean'],
                'full_week' => ['required', 'boolean'],
                'available_from_time1' => ['required_if:full_day,0'],
                'available_to_time1' => ['required_if:full_day,0'],
                'status' => ['required', 'boolean'],
                'is_addon' => ['required', 'boolean']
            ];
            if ($input['discount_type'] == 'percentage') {
                $rules['discount_percentage'] = 'required|numeric|between:0.1,99.99';
            }
            if ($input['discount_type'] == 'amount') {
                $rules['discount_amount'] = 'required|numeric|min:1';
            }
            if ($input['bar_code'] != '') {
                $rules['bar_code'] = 'max:50|regex:/^[a-zA-Z0-9]+$/';
            }
            if ($input['sku_code'] != '') {
                $rules['sku_code'] = 'max:50|regex:/^[a-zA-Z0-9]+$/';
            }
            if ($input['aisle_index'] != '') {
                $rules['aisle_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            if ($input['shelf_index'] != '') {
                $rules['shelf_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            if ($input['brand_index'] != '') {
                $rules['brand_index'] = 'max:50|regex:/^[a-zA-Z0-9 \-\.\_\&]+$/';
            }
            $validator = app('validator')->make($input, $rules);
            $validator->after(function ($validator) use ($input) {
                if (isset($input['item_name_en']) && !empty($input['item_name_en']) && isset($input['vendor_id']) && !empty($input['vendor_id']) && isset($input['id']) && !empty($input['id'])) {
                    $item_name_slug = Str::slug($input['item_name_en']);
                    /* to check the name is already exist or not */
                    $check_name_exist = Menu_items::checkNameExist($item_name_slug, $input['vendor_id'], $input['id']);
                    if (!empty($check_name_exist)) {
                        $validator->errors()->add('item_name_en', 'The Item Name has already been taken.');
                    }
                }
                /* If the discount type is amount, so to check the discount amount */
                if (isset($input['discount_type']) && $input['discount_type'] == 'amount') {
                    if ($input['price'] < $input['discount_amount']) {
                        $validator->errors()->add('Discount amount should be lesser than menu item price');
                    }
                }
                /* To check the available time valid or not */
                if (isset($input['full_day']) && $input['full_day'] == 0) {
                    for ($i = 1; $i < 5; $i++) {
                        if (($input['available_from_time' . $i] <> "" || $input['available_to_time' . $i] <> "") && ($input['available_from_time' . $i] == "" || $input['available_to_time' . $i] == "")) {
                            $validator->errors()->add('available_from_time' . $i, 'Both from time and to time is mandatory.');
                        }
                        if (!empty($input['available_from_time' . $i]) && !empty($input['available_to_time' . $i])) {
                            $fromTime1 = strtotime($input['available_from_time' . $i]);
                            $toTime1 = strtotime($input['available_to_time' . $i]);
                            if (intval($fromTime1) >= intval($toTime1)) {
                                $validator->errors()->add('available_from_time' . $i, 'Available To Time' . $i . ' should be greater than from time.');
                            }
                            for ($j = 1; $j < 5; $j++) {
                                if (($i <> $j) && !empty($input['available_from_time' . $j]) && !empty($input['available_to_time' . $j])) {
                                    $fromTime2 = strtotime($input['available_from_time' . $j]);
                                    $toTime2 = strtotime($input['available_to_time' . $j]);
                                    if (($fromTime1 > $fromTime2 && $fromTime1 <= $toTime2) || ($toTime1 > $fromTime2 && $toTime1 <= $toTime2)) {
                                        $validator->errors()->add('available_from_time' . $i, 'Available Time' . $i . '-Please provide proper from and to timings.');
                                    }
                                }
                            }
                        }
                    }
                }

                /* To check the add-on category name already exist or not */
                if ($input['is_addon'] == 1) {
                    if (isset($input['addon_category']) && count($input['addon_category']) > 0) {
                        $check_category_empty = 0;
                        if (!empty($input['addon_category'])) {
                            $i = 1;
                            foreach ($input['addon_category'] as $addon_key => $addon_val) {
                                if (empty($addon_val)) {
                                    $check_category_empty = 1;
                                    $validator->errors()->add('addon_category[' . $addon_key . ']', 'Add-On Category-' . $i . ' field is required.');
                                }
                                /* To check the add-on sort-order is required or not condition */
                                if (isset($input['addon_category_sort_order'][$addon_key]) && !empty($input['addon_category_sort_order'][$addon_key])) {
                                    $addon_category_sort_order = trim($input['addon_category_sort_order'][$addon_key]);
                                    if (!preg_match("/^[0-9]+$/", $addon_category_sort_order)) {
                                        $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                    }
                                } else {
                                    $validator->errors()->add('addon_category_sort_order[' . $addon_key . ']', 'Add-On Category Sort Order-' . $i . ' field is required.');
                                }
                                /* To get the add-on category details */
                                $addOnCategoryDetail = Addon_categories::getAddOnCategoryDetails($addon_val);
                                if (!empty($addOnCategoryDetail)) {
                                    $no_of_mandatory = $addOnCategoryDetail->no_of_mandatory;
                                    if ($addOnCategoryDetail->mandatory == 1) {
                                        /* To check the add-on name is required or not condition */
                                        if (isset($input['addon_name'][$addon_key]) && count($input['addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_name'][$addon_key] as $name_key => $name_val) {
                                                $name_val = trim($name_val);
                                                if (empty($name_val)) {
                                                    $validator->errors()->add('addon_name[' . $addon_key . '][' . $name_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[a-zA-Z0-9 _\-.,:!@#$%*&()']+$/", $name_val)) {
                                                    //if(preg_match("/^[a-zA-Z0-9 _-.,:"']+$/", $name_val) === 0)
                                                    $validator->errors()->add('addon_name[' . $addon_key . '][' . $name_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                            $addOnName = isset($input['addon_name'][$addon_key]) ? $input['addon_name'][$addon_key] : array();
                                            $addOnStatus = isset($input['addon_status'][$addon_key]) ? $input['addon_status'][$addon_key] : array();
                                            if (count($addOnName) > count(array_unique($addOnName))) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Duplicates occured in add-on names (' . $addOnCategoryDetail->name_en . ')');
                                            } elseif ($no_of_mandatory > count($addOnStatus)) {
                                                $validator->errors()->add('addon_status[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . ' Active.(For ' . $addOnCategoryDetail->name_en . ')');
                                            } elseif ($no_of_mandatory > ($j - 1)) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . '(For ' . $addOnCategoryDetail->name_en . ')');
                                            }
                                        } else {
                                            $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on arabic name is required or not condition */
                                        if (isset($input['arabic_addon_name'][$addon_key]) && count($input['arabic_addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['arabic_addon_name'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-' . $j . ' field is required.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on sort-order is required or not condition */
                                        if (isset($input['addon_sort_order'][$addon_key]) && count($input['addon_sort_order'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_sort_order'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9]+$/", $order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on price is required or not condition */
                                        if (isset($input['addon_price'][$addon_key]) && count($input['addon_price'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_price'][$addon_key] as $price_key => $price_val) {
                                                $price_val = trim($price_val);
                                                if (empty($price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9.]+$/", $price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price -' . $i . '-1 field is required.');
                                        }
                                    } else {//Not mandatory

                                        /* To check the add-on name is required or not condition */
                                        if (isset($input['addon_name'][$addon_key]) && count($input['addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_name'][$addon_key] as $name_key => $name_val) {
                                                $name_val = trim($name_val);
                                                if (empty($name_val)) {
                                                    $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[a-zA-Z0-9 _\-.,:!@#$%*&()']+$/", $name_val)) {
                                                    //if(preg_match("/^[a-zA-Z0-9 _-.,:"']+$/", $name_val) === 0)
                                                    $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                            $addOnName = isset($input['addon_name'][$addon_key]) ? $input['addon_name'][$addon_key] : array();
                                            $addOnStatus = isset($input['addon_status'][$addon_key]) ? $input['addon_status'][$addon_key] : array();
                                            if (count($addOnName) > count(array_unique($addOnName))) {
                                                $validator->errors()->add('addon_name[' . $addon_key . ']', 'Duplicates occured in add-on names (' . $addOnCategoryDetail->name_en . ')');
                                            }
                                            if ($addOnCategoryDetail->type == 'Exactly' && $no_of_mandatory > count($addOnStatus)) {
                                                $validator->errors()->add('addon_status[' . $addon_key . ']', 'Your add-ons should be minimum ' . $no_of_mandatory . ' Active.(For ' . $addOnCategoryDetail->name_en . ')');
                                            }
                                        } else {
                                            $validator->errors()->add('addon_name[' . $addon_key . ']', 'Add-On Name-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on arabic name is required or not condition */
                                        if (isset($input['arabic_addon_name'][$addon_key]) && count($input['arabic_addon_name'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['arabic_addon_name'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-' . $j . ' field is required.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('arabic_addon_name[' . $addon_key . ']', 'Arabic Add-On Name -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on sort-order is required or not condition */
                                        if (isset($input['addon_sort_order'][$addon_key]) && count($input['addon_sort_order'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_sort_order'][$addon_key] as $order_key => $order_val) {
                                                $order_val = trim($order_val);
                                                if (empty($order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9]+$/", $order_val)) {
                                                    $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_sort_order[' . $addon_key . ']', 'Add-On Sort Order-' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on price is required or not condition */
                                        if (isset($input['addon_price'][$addon_key]) && count($input['addon_price'][$addon_key]) > 0) {
                                            /* To check the value is empty or not */
                                            $j = 1;
                                            foreach ($input['addon_price'][$addon_key] as $price_key => $price_val) {
                                                $price_val = trim($price_val);
                                                if (empty($price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is required.');
                                                } elseif (!preg_match("/^[0-9.]+$/", $price_val)) {
                                                    $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price-' . $i . '-' . $j . ' field is invalid.');
                                                }
                                                $j++;
                                            }
                                        } else {
                                            $validator->errors()->add('addon_price[' . $addon_key . ']', 'Add-On Price -' . $i . '-1 field is required.');
                                        }
                                        /* To check the add-on status is required or not condition */
                                        if (!isset($input['addon_status'][$addon_key])) {
                                            $validator->errors()->add('addon_status[' . $addon_key . ']', 'You need to activate atleast one Add-On Status (For ' . $addOnCategoryDetail->name_en . ')');
                                        }
                                    }
                                } else {
                                    $validator->errors()->add('addon_category[' . $addon_key . ']', 'Invalid Add-On Category-' . $i);
                                }
                                $i++;
                            }
                        }
                        if ($check_category_empty == 0 && (count($input['addon_category']) <> count(array_unique($input['addon_category'])))) {
                            $validator->errors()->add('addon_category', 'Add-On Category is already existed.');
                        }
                    } else {
                        $validator->errors()->add('addon_category', 'Add-On Category-1 field is required.');
                        $validator->errors()->add('addon_name', 'Add-On Name-1 field is required.');
                        $validator->errors()->add('addon_price', 'Add-On Price-1 field is required.');
                        $validator->errors()->add('addon_sort_order', 'Add-On Sort Order-1 field is required.');
                    }
                }
            });
            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }

                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            //return ResponseBuilder::responseResult($this->failureStatus, $credentials);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                /* To update the menu masters table */
                $menu_item_master_id = Menu_item_masters::Insert_menu_item_master($input);
                $input['menu_item_master_id'] = $menu_item_master_id;
                //$primary_tag = Tag_masters::Insert_tags($input, 1);
                //$input['primary_tag'] = $primary_tag;
                $input['primary_tag_name'] = Tag_masters::Get_primary_tag($input['primary_tag']);
                //$secondary_tags = Tag_masters::Insert_tags($input, 2);
                //$input['secondary_tags'] = $secondary_tags;
				$input['secondary_tags'] = implode(',',$input['secondary_tags']);
                $input['secondary_tag_names'] = Tag_masters::Get_secondary_tags($input['secondary_tags']);

                 Menu_items::Update_menu_item($input);
                $menuItemDetail = Menu_items::getMenuItemDetails($input['id']);
                Addon_menu_item_mappings::updateAddOnMenuItemMapping($input);
                /* To update the vendor primary tags */
                //$primaryTagList = Menu_items::primaryTagList($input['vendor_id']);
                //Vendors::updateVendorPrimaryTags($input['vendor_id'], $primaryTagList);
                //$data['akeed_menu_item_id'] = $menuItemDetail->akeed_menu_item_id;
                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item Updated Successfully');//, $data
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* Menu Item details */
    public function details(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['id'] = isset($input['id']) ? trim($input['id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:menu_items,id'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $menuItemDetail = Menu_items::getMenuItemDetails($input['id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item Details has been fetched successfully', $menuItemDetail);

            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the menu item status */
    public function updateStatus(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:menu_items,id'],
                'status' => ['required', 'boolean', 'numeric'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                Menu_items::updateStatus($input['id'], $input['status']);
                $menuItemDetail = Menu_items::getMenuItemDetails($input['id']);
                //$data['akeed_menu_item_id'] = $menuItemDetail->akeed_menu_item_id;
                return ResponseBuilder::responseResult($this->successStatus, 'Manu Item status changed successfully');//, $data
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To get the add-on category list based on menu_id */
    public function menuAddOnCategory(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:menu_items,id'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $addOnCategoryList = Addon_menu_item_mappings::addOnCategoryList($input['id']);
                $addOnCategory = array();
                if (!empty($addOnCategoryList)) {
                    $i = 0;
                    //$addOnCategoryDetails = array();
                    foreach ($addOnCategoryList as $category) {
                        $addOnCategory[$i]['categoryId'] = $category['addon_category_id'];
                        $addOnCategory[$i]['addon_category_sort_order'] = $category['addon_category_sort_order'];
                        /* To get the category details */
                        $addOnCategoryDetail = Addon_menu_item_mappings::addOnCategoryDetail($input['id'], $category['addon_category_id']);
                        if (!empty($addOnCategoryDetail)) {
                            $j = 0;
                            foreach ($addOnCategoryDetail as $c_detail) {
                                $addOnCategory[$i]['category_detail'][$j]['addon_name_en'] = $c_detail['addon_name_en'];
                                $addOnCategory[$i]['category_detail'][$j]['addon_name_ar'] = $c_detail['addon_name_ar'];
                                $addOnCategory[$i]['category_detail'][$j]['price'] = $c_detail['price'];
                                $addOnCategory[$i]['category_detail'][$j]['sort_order'] = $c_detail['sort_order'];
                                $addOnCategory[$i]['category_detail'][$j]['status'] = $c_detail['status'];
                                $j++;
                            }
                        }
                        $i++;
                    }
                }
                return ResponseBuilder::responseResult($this->successStatus, 'Add-On Category List', $addOnCategory);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the menu item status 
    public function updateAkeedMenuId(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:menu_items,id'],
                'akeed_menu_item_id' => ['required', 'numeric'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                Menu_items::updateAkeedMenuId($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item - Akeed Id Added successfully');
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }*/

    /* To update the menu item available option */
    public function menuItemAvailable(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'id' => ['required', 'numeric'],
                'status' => ['required', 'boolean', 'numeric'],
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            $status = ($input['status'] == '0')?'1':'0';
            $menu_item = Menu_items::updateStatususingAkeedMenuItem($input['id'], $status);
            if ($menu_item) {
                return ResponseBuilder::responseResult($this->successStatus, 'Menu Item status changed successfully');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Menu Item is not there in microservice');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
