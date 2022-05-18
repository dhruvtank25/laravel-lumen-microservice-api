<?php

namespace App\Http\V_1_0_0\InsertUpdate\Controllers\Orders;

use Illuminate\Http\Request;
use App\Http\Controller;

use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Notifications;
use App\Helpers\ResponseBuilder;

//use Cache;

class NotificationController extends Controller
{
    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * reject order api
     *
     * @return \Illuminate\Http\Response
     */
    public function storeNotification(Request $request)
    {
        try {
            $input = $request->all();
            $input['UserId'] = isset($input['UserId']) ? $input['UserId'] : '';
            $input['Title'] = isset($input['Title']) ? $input['Title'] : '';
            $input['MsgText'] = isset($input['MsgText']) ? $input['MsgText'] : '';
            Notifications::storeNotification($input);
            return ResponseBuilder::responseResult($this->successStatus, 'record updated successfully');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
