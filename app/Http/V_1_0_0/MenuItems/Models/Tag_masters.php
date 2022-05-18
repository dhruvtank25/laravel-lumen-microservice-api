<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag_masters extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_slug', 'name', 'created_by', 'updated_by'
    ];

    /* To insert the tags master */
    public static function Insert_tags($input, $type)
    {
        if ($type == 1) {
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
                        $id = $tag_master->id;
                    } else {
                        $id = $other_tag_exist->id;
                    }
                } else {
                    $id = $tag_exist->id;
                }
            }
            return $id;
        } elseif ($type == 2) {
            $ids = '';
            if (!empty($input['secondary_tags'])) {
                foreach ($input['secondary_tags'] as $tag) {
                    /* To check the id is valid or not */
                    $tag_exist = Tag_masters::where('id', '=', $tag)->first();
                    if (empty($tag_exist)) {
                        /* To check the tags is already exist or not */
                        $other_tag_exist = Tag_masters::where('name_slug', '=', Str::slug($tag))->first();
                        if (empty($other_tag_exist)) {
                            $tag_master = new Tag_masters;
                            $tag_master->name_slug = Str::slug($tag);;
                            $tag_master->name = $tag;
                            $tag_master->created_by = $input['authentication_id'];
                            $tag_master->save();
                            $ids .= $tag_master->id . ',';
                        } else {
                            $ids .= $other_tag_exist->id . ',';
                        }
                    } else {
                        $ids .= $tag_exist->id . ',';
                    }
                }
            }
            return rtrim($ids, ',');
        }
    }

    public static function Get_primary_tag($primary_tag)
    {
        $tags = Tag_masters::where('id', '=', $primary_tag)->first();
        if (!empty($tags)) {
            return $tags->name;
        } else {
            return '-';
        }
    }

    public static function Get_secondary_tags($secondary_tags)
    {
        $all_tags = explode(',', $secondary_tags);
        $s_tags = '';
        if (!empty($all_tags)) {
            foreach ($all_tags as $tag) {
                $tags = Tag_masters::where('id', '=', $tag)->first();
                if (!empty($tags)) {
                    $s_tags .= $tags->name . ',';
                }
            }
            return rtrim($s_tags, ',');
        } else {
            return '-';
        }
    }

    public static function getAllTags()
    {
        $all_tags = Tag_masters::select('id','name')->get();
        $tags = array();
        if(!empty($all_tags)){
            foreach($all_tags as $key => $val){
                $tags[] = $val->name;
            }
        }
        return $tags;
    }

    /* To insert the tags master */
    public static function Insert_excel_tags($input, $type)
    {
        if ($type == 1) {
            if (!empty($input['primary_tag'])) {
                    /* To check the tags is already exist or not */
                    $other_tag_exist = Tag_masters::where('name_slug', '=', Str::slug($input['primary_tag']))->first();
                    if (empty($other_tag_exist)) {
                        $tag_master = new Tag_masters;
                        $tag_master->name_slug = Str::slug($input['primary_tag']);;
                        $tag_master->name = $input['primary_tag'];
                        $tag_master->created_by = $input['authentication_id'];
                        $tag_master->save();
                        $id = $tag_master->id;
                    } else {
                        $id = $other_tag_exist->id;
                    }
            }
            return $id;
        } elseif ($type == 2) {
            $ids = '';
            if (!empty($input['secondary_tags'])) {
                $input['secondary_tags'] = explode(',',$input['secondary_tags']);
                foreach ($input['secondary_tags'] as $tag) {
                    /* To check the tags is already exist or not */
                    $other_tag_exist = Tag_masters::where('name_slug', '=', Str::slug($tag))->first();
                    if (empty($other_tag_exist)) {
                        $tag_master = new Tag_masters;
                        $tag_master->name_slug = Str::slug($tag);;
                        $tag_master->name = $tag;
                        $tag_master->created_by = $input['authentication_id'];
                        $tag_master->save();
                        $ids .= $tag_master->id . ',';
                    } else {
                        $ids .= $other_tag_exist->id . ',';
                    }
                }
            }
            return rtrim($ids, ',');
        }
    }

    /* To insert the tags master */
    public static function Insert_secondary_tags($input)
    {
        $ids = '';
        $tag_array = array();
        for($st=1;$st<=10;$st++){
             /* To check the tags is already exist or not */
             $stag = $input['secondary_tags'.$st];
             if(!empty($stag)){
                $other_tag_exist = Tag_masters::where('name_slug', '=', Str::slug($stag))->first(); 
                if (empty($other_tag_exist)) {
                    $tag_master = new Tag_masters;
                    $tag_master->name_slug = Str::slug($tag);;
                    $tag_master->name = $tag;
                    $tag_master->created_by = $input['authentication_id'];
                    $tag_master->save();
                    $ids .= $tag_master->id . ',';
                } else {
                    if(!in_array($other_tag_exist->id,$tag_array)){
                        $tag_array[] = $other_tag_exist->id;
                        $ids .= $other_tag_exist->id . ',';
                    }
                }
             }
        }
        return $ids;
    }
}
