<?php

namespace App\Http\V_1_0_0\GlobalSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class General_settings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'name', 'code', 'value', 'is_image'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public static function getsetting($code)
    {
        $setting = General_settings::select('id', 'name', 'code', 'value', 'is_image')
            ->where('code',$code);

        return $setting->first();
    }
    /* To send the mail */
    public static function sendMail($input)
    {
        $to_name = $input['name'];
        $to_email = $input['email'];
        $subject = $input['subject'];
        $message = $input['message'];
        $from_email = "info@akeedapp.com";

        $template = '';
        if ($input['template'] == 'accept_order') {
            $template = 'emails.order_accept';
            $data['order_message'] = $message;
            $data['order_item_details'] = $input['order_details'];
        } elseif ($input['template'] == 'reject_order') {
            $template = 'emails.order_reject';
            $data['order_message'] = $message;
        }
        /* Mail::send($template, $data, function ($message) use ($to_name, $to_email, $subject, $from_email) {
            $message->to($to_email, $to_name)
                ->subject($subject);
            $message->from($from_email, 'Akeed');
        }); */

        return true;
    }
}
