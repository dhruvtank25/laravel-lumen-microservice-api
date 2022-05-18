<?php

namespace App\Http\V_1_0_0\VendorTimings\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Helpers\SendSms;

class Vendors extends Model
{
    use Authenticatable, Authorizable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendor_id','sunday_from_time1', 'sunday_to_time1','sunday_from_time2','sunday_to_time2','sunday_from_time3','sunday_to_time3', 'monday_from_time1', 'monday_to_time1','monday_from_time2','monday_to_time2','monday_from_time3','monday_to_time3', 'tuesday_from_time1', 'tuesday_to_time1','tuesday_from_time2','tuesday_to_time2','tuesday_from_time3','tuesday_to_time3', 'wednesday_from_time1', 'wednesday_to_time1','wednesday_from_time2','wednesday_to_time2','wednesday_from_time3','wednesday_to_time3', 'thursday_from_time1', 'thursday_to_time1','thursday_from_time2','thursday_to_time2','thursday_from_time3','thursday_to_time3', 'friday_from_time1', 'friday_to_time1','friday_from_time2','friday_to_time2','friday_from_time3','friday_to_time3', 'saturday_from_time1', 'saturday_to_time1','saturday_from_time2','saturday_to_time2','saturday_from_time3','saturday_to_time3'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function getTimingList($vendor_id)
    {
        $timing = Vendors::select('id as vendor_id', 'sunday_from_time1', 'sunday_to_time1','sunday_from_time2','sunday_to_time2','sunday_from_time3','sunday_to_time3', 'sunday_from_time4', 'sunday_to_time4', 'monday_from_time1', 'monday_to_time1','monday_from_time2','monday_to_time2','monday_from_time3','monday_to_time3', 'monday_from_time4','monday_to_time4', 'tuesday_from_time1', 'tuesday_to_time1','tuesday_from_time2','tuesday_to_time2','tuesday_from_time3','tuesday_to_time3', 'tuesday_from_time4','tuesday_to_time4', 'wednesday_from_time1', 'wednesday_to_time1','wednesday_from_time2','wednesday_to_time2','wednesday_from_time3','wednesday_to_time3', 'wednesday_from_time4','wednesday_to_time4', 'thursday_from_time1', 'thursday_to_time1','thursday_from_time2','thursday_to_time2','thursday_from_time3','thursday_to_time3', 'thursday_from_time4','thursday_to_time4', 'friday_from_time1', 'friday_to_time1','friday_from_time2','friday_to_time2','friday_from_time3','friday_to_time3','friday_from_time4','friday_to_time4', 'saturday_from_time1', 'saturday_to_time1','saturday_from_time2','saturday_to_time2','saturday_from_time3','saturday_to_time3','saturday_from_time4','saturday_to_time4','akeed_vendor_id')
            ->where('id', '=', $vendor_id);

        return $timing->get();
    }

    public static function Update_timing($input)
    {
        $timing = Vendors::where('id', "=", $input['vendor_id'])->first();
        $timing->sunday_from_time1 = ($input['sunday_from_time1'] != '')?date("H:i", strtotime($input['sunday_from_time1'])):NULL;
        $timing->sunday_to_time1 = ($input['sunday_to_time1'] != '')?date("H:i", strtotime($input['sunday_to_time1'])):NULL;
        $timing->sunday_from_time2 = ($input['sunday_from_time2'] != '')?date("H:i", strtotime($input['sunday_from_time2'])):NULL;
        $timing->sunday_to_time2 = ($input['sunday_to_time2'] != '')?date("H:i", strtotime($input['sunday_to_time2'])):NULL;
        $timing->sunday_from_time3 = ($input['sunday_from_time3'] != '')?date("H:i", strtotime($input['sunday_from_time3'])):NULL;
        $timing->sunday_to_time3 = ($input['sunday_to_time3'] != '')?date("H:i", strtotime($input['sunday_to_time3'])):NULL;
        $timing->sunday_from_time4 = ($input['sunday_from_time4'] != '')?date("H:i", strtotime($input['sunday_from_time4'])):NULL;
        $timing->sunday_to_time4 = ($input['sunday_to_time4'] != '')?date("H:i", strtotime($input['sunday_to_time4'])):NULL;
        $timing->monday_from_time1 = ($input['monday_from_time1'] != '')?date("H:i", strtotime($input['monday_from_time1'])):NULL;
        $timing->monday_to_time1 = ($input['monday_to_time1'] != '')?date("H:i", strtotime($input['monday_to_time1'])):NULL;
        $timing->monday_from_time2 = ($input['monday_from_time2'] != '')?date("H:i", strtotime($input['monday_from_time2'])):NULL;
        $timing->monday_to_time2 = ($input['monday_to_time2'] != '')?date("H:i", strtotime($input['monday_to_time2'])):NULL;
        $timing->monday_from_time3 = ($input['monday_from_time3'] != '')?date("H:i", strtotime($input['monday_from_time3'])):NULL;
        $timing->monday_to_time3 = ($input['monday_to_time3'] != '')?date("H:i", strtotime($input['monday_to_time3'])):NULL;
        $timing->monday_from_time4 = ($input['monday_from_time4'] != '')?date("H:i", strtotime($input['monday_from_time4'])):NULL;
        $timing->monday_to_time4 = ($input['monday_to_time4'] != '')?date("H:i", strtotime($input['monday_to_time4'])):NULL;
        $timing->tuesday_from_time1 = ($input['tuesday_from_time1'] != '')?date("H:i", strtotime($input['tuesday_from_time1'])):NULL;
        $timing->tuesday_to_time1 = ($input['tuesday_to_time1'] != '')?date("H:i", strtotime($input['tuesday_to_time1'])):NULL;
        $timing->tuesday_from_time2 = ($input['tuesday_from_time2'] != '')?date("H:i", strtotime($input['tuesday_from_time2'])):NULL;
        $timing->tuesday_to_time2 = ($input['tuesday_to_time2'] != '')?date("H:i", strtotime($input['tuesday_to_time2'])):NULL;
        $timing->tuesday_from_time3 = ($input['tuesday_from_time3'] != '')?date("H:i", strtotime($input['tuesday_from_time3'])):NULL;
        $timing->tuesday_to_time3 = ($input['tuesday_to_time3'] != '')?date("H:i", strtotime($input['tuesday_to_time3'])):NULL;
        $timing->tuesday_from_time4 = ($input['tuesday_from_time4'] != '')?date("H:i", strtotime($input['tuesday_from_time4'])):NULL;
        $timing->tuesday_to_time4 = ($input['tuesday_to_time4'] != '')?date("H:i", strtotime($input['tuesday_to_time4'])):NULL;
        $timing->wednesday_from_time1 = ($input['wednesday_from_time1'] != '')?date("H:i", strtotime($input['wednesday_from_time1'])):NULL;
        $timing->wednesday_to_time1 = ($input['wednesday_to_time1'] != '')?date("H:i", strtotime($input['wednesday_to_time1'])):NULL;
        $timing->wednesday_from_time2 = ($input['wednesday_from_time2'] != '')?date("H:i", strtotime($input['wednesday_from_time2'])):NULL;
        $timing->wednesday_to_time2 = ($input['wednesday_to_time2'] != '')?date("H:i", strtotime($input['wednesday_to_time2'])):NULL;
        $timing->wednesday_from_time3 = ($input['wednesday_from_time3'] != '')?date("H:i", strtotime($input['wednesday_from_time3'])):NULL;
        $timing->wednesday_to_time3 = ($input['wednesday_to_time3'] != '')?date("H:i", strtotime($input['wednesday_to_time3'])):NULL;
        $timing->wednesday_from_time4 = ($input['wednesday_from_time4'] != '')?date("H:i", strtotime($input['wednesday_from_time4'])):NULL;
        $timing->wednesday_to_time4 = ($input['wednesday_to_time4'] != '')?date("H:i", strtotime($input['wednesday_to_time4'])):NULL;
        $timing->thursday_from_time1 = ($input['thursday_from_time1'] != '')?date("H:i", strtotime($input['thursday_from_time1'])):NULL;
        $timing->thursday_to_time1 = ($input['thursday_to_time1'] != '')?date("H:i", strtotime($input['thursday_to_time1'])):NULL;
        $timing->thursday_from_time2 = ($input['thursday_from_time2'] != '')?date("H:i", strtotime($input['thursday_from_time2'])):NULL;
        $timing->thursday_to_time2 = ($input['thursday_to_time2'] != '')?date("H:i", strtotime($input['thursday_to_time2'])):NULL;
        $timing->thursday_from_time3 = ($input['thursday_from_time3'] != '')?date("H:i", strtotime($input['thursday_from_time3'])):NULL;
        $timing->thursday_to_time3 = ($input['thursday_to_time3'] != '')?date("H:i", strtotime($input['thursday_to_time3'])):NULL;
        $timing->thursday_from_time4 = ($input['thursday_from_time4'] != '')?date("H:i", strtotime($input['thursday_from_time4'])):NULL;
        $timing->thursday_to_time4 = ($input['thursday_to_time4'] != '')?date("H:i", strtotime($input['thursday_to_time4'])):NULL;
        $timing->friday_from_time1 = ($input['friday_from_time1'] != '')?date("H:i", strtotime($input['friday_from_time1'])):NULL;
        $timing->friday_to_time1 = ($input['friday_to_time1'] != '')?date("H:i", strtotime($input['friday_to_time1'])):NULL;
        $timing->friday_from_time2 = ($input['friday_from_time2'] != '')?date("H:i", strtotime($input['friday_from_time2'])):NULL;
        $timing->friday_to_time2 = ($input['friday_to_time2'] != '')?date("H:i", strtotime($input['friday_to_time2'])):NULL;
        $timing->friday_from_time3 = ($input['friday_from_time3'] != '')?date("H:i", strtotime($input['friday_from_time3'])):NULL;
        $timing->friday_to_time3 = ($input['friday_to_time3'] != '')?date("H:i", strtotime($input['friday_to_time3'])):NULL;
        $timing->friday_from_time4 = ($input['friday_from_time4'] != '')?date("H:i", strtotime($input['friday_from_time4'])):NULL;
        $timing->friday_to_time4 = ($input['friday_to_time4'] != '')?date("H:i", strtotime($input['friday_to_time4'])):NULL;
        $timing->saturday_from_time1 = ($input['saturday_from_time1'] != '')?date("H:i", strtotime($input['saturday_from_time1'])):NULL;
        $timing->saturday_to_time1 = ($input['saturday_to_time1'] != '')?date("H:i", strtotime($input['saturday_to_time1'])):NULL;
        $timing->saturday_from_time2 = ($input['saturday_from_time2'] != '')?date("H:i", strtotime($input['saturday_from_time2'])):NULL;
        $timing->saturday_to_time2 = ($input['saturday_to_time2'] != '')?date("H:i", strtotime($input['saturday_to_time2'])):NULL;
        $timing->saturday_from_time3 = ($input['saturday_from_time3'] != '')?date("H:i", strtotime($input['saturday_from_time3'])):NULL;
        $timing->saturday_to_time3 = ($input['saturday_to_time3'] != '')?date("H:i", strtotime($input['saturday_to_time3'])):NULL;
        $timing->saturday_from_time4 = ($input['saturday_from_time4'] != '')?date("H:i", strtotime($input['saturday_from_time4'])):NULL;
        $timing->saturday_to_time4 = ($input['saturday_to_time4'] != '')?date("H:i", strtotime($input['saturday_to_time4'])):NULL;
        $timing->save();
        return $timing->first();
    }

}
