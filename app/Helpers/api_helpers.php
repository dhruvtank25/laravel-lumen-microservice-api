<?php

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\OptionsPriorities;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
//use FCM;

//define('GOOGLE_API_KEY', 'AAAAjB8yfwU:APA91bFdXlDDusv6xjxiwHSiJJRenxHgjuDNsrz3d0yTE2ECAXJj7YTYbD8foz7Mr0_skMRzbnQdUWXubO8NPQ2f8LbOe5R3cD9FfSO3b-nPvNAwMRYmu8ZQ9Pw38odgC3itl2ozqFWx');

if (!function_exists('send_push_notification')) {

    function send_push_notification($device_type, $token, $title, $message, $data_array)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);
        $optionBuilder->setPriority(OptionsPriorities::high);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        if ($device_type === 'android') {

            $notificationBuilder->setBody($message)->setSound('default')->setClickAction('.MainActivity')->setBadge(1);

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($data_array);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();
        } else {
            $notificationBuilder->setBody($message)->setSound('default')->setClickAction('ios.package.name')->setBadge(1);

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($data_array);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();
        }

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        $notification_response = 0;
//        To check the notification sent or not
        if ($downstreamResponse->numberSuccess() == 1) {
            $notification_response = 1;
        }
        return $notification_response;
        /*$url = 'https://fcm.googleapis.com/fcm/send';
        $notification_type = $data_array['notification_type'];
        //if ($notification_type == 1) {
            $notification_body = $message . ',' . $notification_type . ',' . $data_array['meeting_id'];
//        } else {
//            $notification_body = $message;
//        }
        $data_array['body'] = $notification_body;
        $fields = array(
            'to' => $token,
            'data' => $data_array,
        );

        $headers = array(
            'Authorization:key='.GOOGLE_API_KEY,
            'Content-Type:application/json'
        );

        // Open connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if(curl_errno($ch)){
            return false;
        }
        curl_close($ch);

        if ($result === FALSE)
            return true;
        else
            return false;*/
    }
}
