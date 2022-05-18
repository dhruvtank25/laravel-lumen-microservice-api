<?php
namespace App\Http\V_1_0_0\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor_primary_tag_mappings extends Model
{

    public static function getVendorPrimaryTagMastersList($vendorId)
    {
        $primaryTag = Vendor_primary_tag_mappings::select('vendor_primary_tag_mappings.tag_id as id', 'tag_masters.name', 'vendor_primary_tag_sort_order')
            ->join('tag_masters','tag_masters.id','=','vendor_primary_tag_mappings.tag_id')
            ->where('vendor_id', $vendorId)->orderby('vendor_primary_tag_sort_order', 'asc');
        return $primaryTag->get();
    }

    /* To update the vendor primary tag mappings */
    public static function updateVendorPrimaryTagsMapping($input)
    {
        Vendor_primary_tag_mappings::select('id')->where('vendor_id', $input['vendor_id'])->delete();

        if (!empty($input['primary_tag'])) {
            foreach ($input['primary_tag'] as $k => $v) {
                $primary_tag_master = new Vendor_primary_tag_mappings;
                $primary_tag_master->vendor_id = $input['vendor_id'];
                $primary_tag_master->tag_id = $v;
                $primary_tag_master->vendor_primary_tag_sort_order = $input['primary_tag_sort_order'][$k];
                $primary_tag_master->created_by = $input['authentication_id'];
                $primary_tag_master->save();
                $primary_tag_master->akeed_tag_mapping_id = $primary_tag_master->id;
                $primary_tag_master->save();
            }
        }
        return true;
    }

}
