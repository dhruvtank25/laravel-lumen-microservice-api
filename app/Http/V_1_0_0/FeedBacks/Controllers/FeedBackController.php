<?php

namespace App\Http\V_1_0_0\FeedBacks\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\FeedBacks\Models\Orders;
use App\Helpers\ResponseBuilder;

//use Cache;

class FeedBackController extends Controller
{
    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */

    public function FeedBackList(Request $request)
    {
        try {
            $input = $request->all();
            $vendor_id = $input['vendor_id'];
            $from = $_REQUEST['order_from'];
            $to = $_REQUEST['order_to'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $key = trim($input['q']);
                $offset = $input['page'];
                $limit = $input['per_page'];
                $feedback_list['feedback_list'] = Orders::getOrderFeedbackList($vendor_id,$key,$offset,$limit,$from,$to);
                $feedback_list['feedback_count'] =Orders::feedbackcount($vendor_id);
                $feedback_list['feedback_filter_count'] =Orders::feedbackFilterCount($vendor_id,$key,$from,$to);
                return ResponseBuilder::responseResult($this->successStatus, 'FeedBack List has been fetched successfully', $feedback_list);

            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /**
     * Create a new token.
     * 
     * @return string
     */

}
