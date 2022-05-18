<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    public static function storeNotification($dataInput)
    {
        $notification = new Notifications();
        $notification->receiver_id = $dataInput['UserId'];
        $notification->title = $dataInput['Title'];
        $notification->title_ar = isset($dataInput['TitleAr'])?$dataInput['TitleAr']:'';
        $notification->message = $dataInput['MsgText'];
        $notification->message_ar = isset($dataInput['MsgTextAr'])?$dataInput['MsgTextAr']:'';
        $notification->save();
        return $notification;
    }
}
