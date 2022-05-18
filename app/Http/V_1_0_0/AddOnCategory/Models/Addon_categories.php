<?php

namespace App\Http\V_1_0_0\AddOnCategory\Models;

use Illuminate\Database\Eloquent\Model;

class Addon_categories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_en', 'name_ar', 'type', 'mandatory', 'no_of_mandatory', 'sort_order', 'status', 'created_by', 'updated_by'
    ];

    /* To get the add-on categories list */
    public static function getAddOnCategoriesList()
    {
        $addon_categories = Addon_categories::select('addon_categories.id as addon_category_id', 'name_en', 'name_ar', 'type', 'mandatory', 'no_of_mandatory', 'sort_order', 'status', 'created_at', 'updated_at');
        return $addon_categories->get();
    }

    public static function getAddOnCategories()
    {
        $addon_categories = Addon_categories::select('addon_categories.id as addon_category_id', 'name_en', 'name_ar', 'type', 'mandatory', 'no_of_mandatory')->where('status','=','1')->orderby('name_en','asc');//To get the active categories
        return $addon_categories->get();
    }

    public static function Insert_addon_category($input)
    {
        $addon_categories = new Addon_categories;
        $addon_categories->name_en = $input['name_en'];
        $addon_categories->name_ar = $input['name_ar'];
        $addon_categories->type = $input['type'];
        $addon_categories->mandatory = $input['mandatory'];
        $addon_categories->no_of_mandatory = $input['no_of_mandatory'];
        $addon_categories->sort_order = $input['sort_order'];
        $addon_categories->status = $input['status'];
        $addon_categories->created_by = $input['authentication_id'];
        $addon_categories->save();
        return $addon_categories->id;
    }

    public static function update_addon_category($input)
    {
        $addon_categories = Addon_categories::find($input['id']);
        $addon_categories->name_en = $input['name_en'];
        $addon_categories->name_ar = $input['name_ar'];
        $addon_categories->type = $input['type'];
        $addon_categories->mandatory = $input['mandatory'];
        $addon_categories->no_of_mandatory = $input['no_of_mandatory'];
        $addon_categories->sort_order = $input['sort_order'];
        $addon_categories->status = $input['status'];
        $addon_categories->updated_by = $input['authentication_id'];
        $addon_categories->save();
        return $addon_categories->first();
    }

    public static function getAddOnCategoryDetails($id)
    {
        $addon_category = Addon_categories::find($id);
        return $addon_category;
    }
    /* To Delete the add on category */
    public static function deleteAddOnCategory($id)
    {
        $addon_category = Addon_categories::where('id', $id)->delete();
        return $addon_category;
    }
    /* To update the add-on category status */
    public static function updateStatus($id, $status)
    {
        $update_category = Addon_categories::find($id);
        $update_category->status = $status;
        $update_category->save();
        return $update_category->first();
    }
}
