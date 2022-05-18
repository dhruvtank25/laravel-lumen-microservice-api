<?php

namespace App\Http\V_1_0_0\Notifications\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'sender_role', 'receiver_id', 'receiver_role', 'title', 'message', 'title_ar', 'message_ar', 'fcm_token', 'device_type', 'sent_status'
    ];

    public static function Send_notification($device_type, $token, $data_array, $dataInput, $language = 'en')
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $dataInput['title'] = $data_array['title'];
        if ($device_type == 'ios') {
            if ($language == 'ar') {
                $data_array['title'] = $data_array['title_ar'];
                $data_array['body'] = $data_array['message_ar'];
            } else {
                $data_array['body'] = $data_array['message'];
            }
            if ($dataInput['receiver_role'] == 1) {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
                $data_array['sound'] = 'Enabled';
            } elseif ($dataInput['receiver_role'] == 2) {//vendor
                $data_array['click_action'] = '.MainActivity';
                $data_array['sound'] = 'horn.caf';
            } elseif ($dataInput['receiver_role'] == 3) {//driver
                $data_array['click_action'] = '.Get_Order_Activity';
                $data_array['Force'] = 'Forced';
                $data_array['sound'] = 'horn.caf';
            }
            $fields = array(
                'to' => $token,
                'notification' => $data_array,
                'data' => $data_array,
            );
        } else {
            if ($dataInput['receiver_role'] == 1) {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
            } elseif ($dataInput['receiver_role'] == 2) {//vendor
                $data_array['click_action'] = '.MainActivity';
            } elseif ($dataInput['receiver_role'] == 3) {//driver
                $data_array['click_action'] = '.NotificationActivity';
            }
            $fields = array(
                'to' => $token,
                'data' => $data_array,
            );
        }
        if ($dataInput['receiver_role'] == 1) {
            $key = env('CUSTOMER_FCM');
        } elseif ($dataInput['receiver_role'] == 2) {
            $key = env('VENDOR_FCM');
        } else {
            $key = env('DRIVER_FCM');
        }
        $headers = array(
            'Authorization:key=' . $key,
            'Content-Type:application/json'
        );

        // Open connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute post
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        if ($result === FALSE)
            $notification_response = 1;
        else
            $notification_response = 0;

        $notification = new Notifications();
        $notification->sender_id = $dataInput['sender_id'];
        $notification->sender_role = $dataInput['sender_role'];
        $notification->receiver_id = $dataInput['receiver_id'];
        $notification->receiver_role = $dataInput['receiver_role'];
        $notification->title = $dataInput['title'];
        $notification->title_ar = $data_array['title_ar'];
        $notification->message = $data_array['message'];
        $notification->message_ar = $data_array['message_ar'];
        $notification->fcm_token = $token;
        $notification->device_type = $device_type;
        $notification->sent_status = ($notification_response == 1) ? '1' : '0';
        $notification->created_by = $dataInput['sender_id'];
        $notification->save();

        /* To check the receiver role is vendor */
        if ($dataInput['receiver_role'] == 2) {
            $key = 'AAAAKNqCRbc:APA91bHIYoJTe9rCFZ8lm8n9fPkMg2LLYqK6K-xK7AzCQolxRKEnXq3kNfc4RRofSjGjjFcRmAsEgCRQ1sKjzYpVYy9cQReNiYI0baxsddNxDjq0f_13gWFEo-rhnRYkE3zNaYra3zJc';
            $headers = array(
                'Authorization:key=' . $key,
                'Content-Type:application/json'
            );

            // Open connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute post
            $result = curl_exec($ch);
            curl_close($ch);
        }

        return $notification_response;
    }

    /* To send the group notification */
    public static function SendGroupNotification($device_type, $token, $data_array)
    {
        $key = 'AAAAKNqCRbc:APA91bHIYoJTe9rCFZ8lm8n9fPkMg2LLYqK6K-xK7AzCQolxRKEnXq3kNfc4RRofSjGjjFcRmAsEgCRQ1sKjzYpVYy9cQReNiYI0baxsddNxDjq0f_13gWFEo-rhnRYkE3zNaYra3zJc';
        $url = 'https://fcm.googleapis.com/fcm/send';
        if ($device_type == 'ios' && !empty($token)) {
            $data_array['body'] = $data_array['message'];
            if ($data_array['receiver_role'] == 1) {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
                $data_array['sound'] = 'Enabled';
                $key = env('CUSTOMER_FCM');
            } elseif ($data_array['receiver_role'] == 2) {//vendor
                $data_array['click_action'] = '.MainActivity';
                $data_array['sound'] = 'horn.caf';
//                $key = env('VENDOR_FCM');
            } elseif ($data_array['receiver_role'] == 3) {//driver
                $data_array['click_action'] = '.Get_Order_Activity';
                $data_array['Force'] = 'Forced';
                $data_array['sound'] = 'horn.caf';
//                $key = env('DRIVER_FCM');
            }
            $headers = array(
                'Authorization:key=' . $key,
                'Content-Type:application/json'
            );
            for ($i = 0; $i < count($token); $i++) {
                $fields = array(
                    'to' => $token[$i],
                    'notification' => $data_array,
                    'data' => $data_array,
                );
                // Open connection
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Execute post
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    //return false;
                }
                curl_close($ch);
            }
            return true;
        } else {
            if ($data_array['receiver_role'] == 1) {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
                $key = env('CUSTOMER_FCM');
            } elseif ($data_array['receiver_role'] == 2) {//vendor
                $data_array['click_action'] = '.MainActivity';
//                $key = env('VENDOR_FCM');
            } elseif ($data_array['receiver_role'] == 3) {//driver
                $data_array['click_action'] = '.NotificationActivity';
//                $key = env('DRIVER_FCM');
            }
            $fields = array(
                'registration_ids' => $token,
                'data' => $data_array,
            );
            $headers = array(
                'Authorization:key=' . $key,
                'Content-Type:application/json'
            );
            // Open connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute post
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);

            if ($result === FALSE)
                return true;
            else
                return false;
        }
    }

    public static function getVendorNotifications($input)
    {
        $list = Notifications::select('title', 'message', DB::raw('DATE_FORMAT(created_at, "%d-%m-%Y %h:%i:%s %p") as formatted_created_at'))->where('receiver_id', $input['authentication_id'])->where('receiver_role', 2)->orderBy('notifications.id', 'desc')->get();
        return $list;
    }

    /* To test the notification */
    public static function SendTestNotification($device_type, $token, $data_array, $appType, $server_key = '')
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        if ($device_type == 'ios') {
            $data_array['body'] = $data_array['message'];
            if ($appType == 'customer') {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
                $data_array['sound'] = 'Enabled';
                $key = env('CUSTOMER_FCM');
            } elseif ($appType == 'vendor') {//vendor
                $data_array['click_action'] = '.MainActivity';
                $data_array['sound'] = 'horn.caf';
                $key = env('VENDOR_FCM');
            } elseif ($appType == 'driver') {//driver
                $data_array['click_action'] = '.Get_Order_Activity';
                $data_array['Force'] = 'Forced';
                $data_array['sound'] = 'horn.caf';
                $key = env('DRIVER_FCM');
            }
            $fields = array(
                'to' => $token,
                'notification' => $data_array,
                'data' => $data_array,
            );
        } else {
            if ($appType == 'customer') {//customer
                $data_array['click_action'] = '.activity.NotificationActivity';
                $key = env('CUSTOMER_FCM');
            } elseif ($appType == 'vendor') {//vendor
                $data_array['click_action'] = '.MainActivity';
                $key = env('VENDOR_FCM');
            } elseif ($appType == 'driver') {//driver
                $data_array['click_action'] = '.NotificationActivity';
                $key = env('DRIVER_FCM');
            }
            $fields = array(
                'to' => $token,
                'data' => $data_array,
            );
        }
        if (!empty($server_key)) {
            $key = $server_key;
        }
        $fields['data']['server_key'] = $key;
        $headers = array(
            'Authorization:key=' . $key,
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute post
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        /* To check the receiver role is vendor */
        if ($appType == 'vendor' && empty($server_key)) {
            $key = 'AAAAKNqCRbc:APA91bHIYoJTe9rCFZ8lm8n9fPkMg2LLYqK6K-xK7AzCQolxRKEnXq3kNfc4RRofSjGjjFcRmAsEgCRQ1sKjzYpVYy9cQReNiYI0baxsddNxDjq0f_13gWFEo-rhnRYkE3zNaYra3zJc';
            $fields['data']['server_key'] = $key;
            $headers = array(
                'Authorization:key=' . $key,
                'Content-Type:application/json'
            );

            // Open connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute post
            $result = curl_exec($ch);
            curl_close($ch);
        }

        return $result;
    }
}
