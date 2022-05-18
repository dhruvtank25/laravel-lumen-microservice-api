<?php

namespace App\Http\V_1_0_0\FeedBacks\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Orders extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'rating', 'review', 'driver_rating'
    ];

    public static function getOrderFeedbackList($vendor_id, $key, $offset, $limit, $from, $to)
    {
        $feedback = Orders::select('id as order_id', 'customer_first_name', 'customer_last_name', 'vendor_first_name_en', DB::raw('DATE_SUB(created_at, INTERVAL 90 MINUTE) as OrderDate'), 'vendor_rating', 'order_review', 'akeed_order_id')
            ->where('vendor_id', '=', $vendor_id)->where('is_rated', '=', 'Yes');

        if ($key != "") {
            $feedback->where(function ($query) use ($key) {
                $query->where('akeed_order_id', 'LIKE', '%' . $key . '%')->orWhere('customer_first_name', 'LIKE', '%' . $key . '%')->orWhere('vendor_rating', 'LIKE', '%' . $key . '%')
                    ->orWhere('order_review', 'LIKE', '%' . $key . '%');
            });
        }

        if ($from != '' && $to != '') {
            $from = date("Y-m-d", strtotime($from));
            $to = date("Y-m-d", strtotime($to));
            $feedback = $feedback->whereBetween(DB::raw('CONVERT(created_at,DATE)'), [DB::raw("CONVERT(DATE_SUB('" . $from . "', INTERVAL 90 MINUTE), DATE)"), DB::raw("CONVERT(DATE_SUB('" . $to . "', INTERVAL 90 MINUTE), DATE)")]);
        }

        $feedback = $feedback->skip($offset)->take($limit);
        return $feedback->get();

    }

    public static function feedbackcount($vendor_id)
    {
        return $feedback = Orders::select('id as order_id', 'customer_first_name', 'customer_last_name', 'vendor_first_name_en', DB::raw('DATE_SUB(created_at, INTERVAL 90 MINUTE) as OrderDate'), 'vendor_rating', 'order_review', 'akeed_order_id')
            ->where('vendor_id', '=', $vendor_id)->where('is_rated', '=', 'Yes')->count();

    }

    public static function feedbackFilterCount($vendor_id, $key, $from, $to)
    {
        $feedback = $feedback = Orders::select('id as order_id', 'customer_first_name', 'customer_last_name', 'vendor_first_name_en', DB::raw('DATE_SUB(created_at, INTERVAL 90 MINUTE) as OrderDate'), 'vendor_rating', 'order_review', 'akeed_order_id')
            ->where('vendor_id', '=', $vendor_id)->where('is_rated', '=', 'Yes');

        if ($key != "") {
            $feedback->where(function ($query) use ($key) {
                $query->where('akeed_order_id', 'LIKE', '%' . $key . '%')->orWhere('customer_first_name', 'LIKE', '%' . $key . '%')->orWhere('vendor_rating', 'LIKE', '%' . $key . '%')
                    ->orWhere('order_review', 'LIKE', '%' . $key . '%');
            });
        }
        if ($from != '' && $to != '') {
            $from = date("Y-m-d", strtotime($from));
            $to = date("Y-m-d", strtotime($to));
            $feedback = $feedback->whereBetween(DB::raw('CONVERT(created_at,DATE)'), [DB::raw("CONVERT(DATE_SUB('" . $from . "', INTERVAL 90 MINUTE), DATE)"), DB::raw("CONVERT(DATE_SUB('" . $to . "', INTERVAL 90 MINUTE), DATE)")]);
        }
        return $feedback->count();
    }

}
