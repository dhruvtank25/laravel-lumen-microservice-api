<?php

namespace App\Http\V_1_0_0\AddOnCategory\Models;

use Illuminate\Database\Eloquent\Model;

class Addon_menu_item_mappings extends Model
{
	/* To check this add-on category id is already exist or not */
	public static function checkAddOnCategoryExist($id)
	{
		$addon_category = Addon_menu_item_mappings::where('addon_category_id', $id)->first();
        return $addon_category;
	}
}
