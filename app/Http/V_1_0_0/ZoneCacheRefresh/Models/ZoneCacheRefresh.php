<?php

namespace App\Http\V_1_0_0\ZoneCacheRefresh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use DB;

class ZoneCacheRefresh extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'latitude', 'longitude', 'vendor_list', 'expiration', 'vendors_ids'
    ];

    protected $table = 'zones_cache';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


    public static function insert_cache($latitude, $longitude, $vendors_list, $vendors_ids)
    {
        $zones_cache = new Zones_cache;
        $zones_cache->latitude = $latitude;
        $zones_cache->longitude = $longitude;
        $zones_cache->vendors_list = $vendors_list;
        $zones_cache->vendors_ids = rtrim($vendors_ids, ',');
        $zones_cache->save();
        return $zones_cache->id;
    }

    public static function get_vendors_from_cache($latitude, $longitude)
    {
        $vendors_list = DB::select("SELECT *,
                            3956 * 2 * ASIN(SQRT( POWER(SIN(('$latitude' -
                            abs( 
                            dest.latitude)) * pi()/180 / 2), 2) + COS('$latitude' * pi()/180 ) * COS( 
                            abs
                            (dest.latitude) *  pi()/180) * POWER(SIN(('$longitude' -
                            dest.longitude) *  pi()/180 / 2), 2) ))
                            as DISTANCE FROM zones_cache dest having DISTANCE < 0.5 ORDER BY DISTANCE LIMIT 1");
        return $vendors_list;
    }


    public static function refreshCache($vendor_id)
    {
        DB::table('zones_cache')->truncate();
        return true;

        $cache_list = DB::table('zones_cache')
            ->whereRaw("FIND_IN_SET($vendor_id,vendors_ids)")
            ->get();
        foreach ($cache_list as $cache) {
            $distance_calculate = env('GLOBAL_DISTANCE_CALCULATE');
            $latitude = $cache->latitude;
            $longitude = $cache->longitude;

            $vendors_list = DB::select("SELECT *,
                            3956 * 2 * ASIN(SQRT( POWER(SIN(('$cache->latitude' -
                            abs( 
                            vendors.latitude)) * pi()/180 / 2), 2) + COS('$cache->latitude' * pi()/180 ) * COS( 
                            abs
                            (vendors.latitude) *  pi()/180) * POWER(SIN(('$cache->longitude' -
                            vendors.longitude) *  pi()/180 / 2), 2) ))
                            as DISTANCE FROM vendors where vendors.verified = '1' and vendors.status = '1' having (DISTANCE < vendors.serving_distance AND DISTANCE < $distance_calculate) ORDER BY vendors.rank,DISTANCE asc LIMIT 99");
            $app_id = env('HERE_APP_ID');
            $app_code = env('HERE_APP_CODE');
            $i = 0;
            $destination_string = '';
            $vendors_ids = '';
            foreach ($vendors_list as $vendors) {
                $destination_string .= '&destination' . $i . '=' . $vendors->latitude . ',' . $vendors->longitude;
                $i++;
            }
            if (!empty($destination_string)) {
                $url = "https://matrix.route.api.here.com/routing/7.2/calculatematrix.json?app_id=${app_id}&app_code=${app_code}&start0=${latitude},${longitude}${destination_string}&mode=fastest;car;traffic:disabled&summaryAttributes=tt,di&MatrixRouteAttributeType=ix";
                $client = new \GuzzleHttp\Client();
                $res = $client->get($url);
                if ($res->getStatusCode() == 200) {
                    $j = $res->getBody();
                    $obj = json_decode($j);
                    $here_map_distance = env('HERE_MAP_DISTANCE_FILTER');
                    foreach ($obj->response->matrixEntry as $key => $distance) {
                        $distance_in_km = $distance->summary->distance / 1000;
                        if ($distance_in_km > $vendors_list[$key]->serving_distance) {
                            unset($vendors_list[$key]);
                        } else {
                            $vendors_list[$key]->distance_heremaps = $distance->summary->distance;
                            $vendors_list[$key]->travel_time_heremaps = $distance->summary->travelTime;
                            $vendors_list[$key]->distance_heremaps_km = ($distance->summary->distance / 1000);
                            $vendors_list[$key]->travel_time_heremaps_min = ($distance->summary->travelTime / 60);
                            $vendors_ids .= $vendors_list[$key]->id . ",";
                        }
                    }
                }
            }
            $vendors_list_json_array = json_encode($vendors_list);
            $cache_list_update = ZoneCacheRefresh::find($cache->id);
            $cache_list_update->vendors_list = $vendors_list_json_array;
            $cache_list_update->vendors_ids = rtrim($vendors_ids, ',');
            $cache_list_update->save();

            if (rtrim($vendors_ids, ',') == "") {
                $cache_list_update->delete();
            }

        }
        return true;
    }
}
