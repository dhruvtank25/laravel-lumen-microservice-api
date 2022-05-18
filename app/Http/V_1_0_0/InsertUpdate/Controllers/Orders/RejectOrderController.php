<?php

namespace App\Http\V_1_0_0\InsertUpdate\Controllers\Orders;

use Illuminate\Http\Request;
use App\Http\Controller;

use App\Http\V_1_0_0\InsertUpdate\Models\Orders\Orders;
use App\Helpers\ResponseBuilder;

//use Cache;

class rejectOrderController extends Controller
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
    public function rejectOrder(Request $request)
    {
        try {
            $input = $request->all();
            $input['orderid'] = isset($input['orderid']) ? $input['orderid'] : '';
            $input['reason'] = isset($input['reason']) ? $input['reason'] : '';
            $orderDetail = Orders::OrderDetail($input['orderid']);
            if ($orderDetail) {
                Orders::RejectOrder($orderDetail->id, $input['reason']);
                return ResponseBuilder::responseResult($this->successStatus, 'record updated successfully');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Order');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
}
