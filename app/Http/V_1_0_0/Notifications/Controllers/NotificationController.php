<?php

namespace App\Http\V_1_0_0\Notifications\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Firebase\JWT\JWT;

use App\Http\V_1_0_0\Notifications\Models\Notifications;
use App\Helpers\ResponseBuilder;

//use Cache;

class NotificationController extends Controller
{
    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

    public function notificationList(Request $request)
    {
        try {
            $input = $request->all();
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $list = Notifications::getVendorNotifications($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Vendor Notification List',$list);
            }
            return ResponseBuilder::responseResult($this->failureStatus, trans('messages.Invalid Credential'));
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
    /* To test the notification */
    public function testNotification(Request $request)
    {
        try {
            $input = $request->all();
            $fcmToken = isset($input['fcm_token'])?$input['fcm_token']:'';
            $deviceType = isset($input['device_type'])?$input['device_type']:'';
            $appType = isset($input['app_type'])?$input['app_type']:'';
            /* To send the push notification to the customer */
            $notitificationTitle = 'Test notification title';
            $notitificationMessage = 'Test notificaiton message';

            $data_array = ['title' => $notitificationTitle, 'message' => $notitificationMessage, 'notification_type' => 7, 'order_id' => 0];
            /* To send the push notification */
            $data['notificationResponse'] = Notifications::SendTestNotification($deviceType, $fcmToken, $data_array, $appType);

            return ResponseBuilder::responseResult($this->successStatus, 'Notification Response', $data);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}