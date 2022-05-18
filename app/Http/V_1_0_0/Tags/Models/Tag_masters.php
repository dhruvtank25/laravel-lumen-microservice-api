<?php

namespace App\Http\V_1_0_0\Tags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
//use APP;


class Tag_masters extends Model
{
    public static function getTagMastersList()
    {
        $tagMasters = Tag_masters::select('id', 'name_slug', 'name');
        return $tagMasters->get();
    }

    /* To get the tag detail */
    public static function getTagMastersDetail($input)
    {
        if (!empty($input['primary_tag'])) {
            /* To check the id is valid or not */
            $tag_exist = Tag_masters::where('id', '=', $input['primary_tag'])->first();
            if (empty($tag_exist)) {
                /* To check the tags is already exist or not */
                $other_tag_exist = Tag_masters::where('name_slug', '=', Str::slug($input['primary_tag']))->first();
                if (empty($other_tag_exist)) {
                    $tag_master = new Tag_masters;
                    $tag_master->name_slug = Str::slug($input['primary_tag']);;
                    $tag_master->name = $input['primary_tag'];
                    $tag_master->created_by = $input['authentication_id'];
                    $tag_master->save();
                    $detail = $tag_master;
                } else {
                    $detail = $other_tag_exist;
                }
            } else {
                $detail = $tag_exist;
            }
            return $detail;
        } else {
            return array();
        }
    }

}
