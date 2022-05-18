<?php

namespace App\Http\V_1_0_0\VendorOrder\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\VendorOrder\Models\Orders;
use App\Http\V_1_0_0\VendorOrder\Models\Order_items;
use App\Helpers\ResponseBuilder;

//use Cache;

class OrderController extends Controller
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
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function orderList(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $from = $input['order_from'];
            $to = $input['order_to'];
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $key = trim($input['q']);
                $offset = $input['page'];
                $limit = $input['per_page'];
                $status = $input['order_status'];
                $order_list['order_list'] = Orders::OrderList( $input['vendor_id'],$key,$offset,$limit,$status,$from,$to);
                $order_list['order_count'] =Orders::ordercount($input['vendor_id']);
                $order_list['order_filter_count'] =Orders::orderFiltercount($input['vendor_id'],$key,$status,$from,$to);
                    return ResponseBuilder::responseResult($this->successStatus, 'Order List',$order_list);

            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch(\Firebase\JWT\ExpiredException $e){
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function orderDetail(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'order_id' => ['required', 'numeric', 'exists:order_items,order_id']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $order_detail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
                if($order_detail) {
                    return ResponseBuilder::responseResult($this->successStatus, 'Order Details',$order_detail);
                } else {
                    return ResponseBuilder::responseResult($this->failureStatus, 'Order not found');
                }
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch(\Firebase\JWT\ExpiredException $e){
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function orderItems(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'order_id' => ['required', 'numeric', 'exists:order_items,order_id']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $order_items= Order_items::orderItem($input['order_id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Order Items', $order_items);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch(\Firebase\JWT\ExpiredException $e){
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function accpetOrder(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['reason'] = isset($input['reason']) ? trim($input['reason']) : '';
            $input['language'] = (isset($input['language']) && $input['language'] == 'ar')?'ar':'en';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'order_id' => ['required', 'numeric', 'exists:orders,id'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'reason' => ['regex:/^[a-zA-Z0-9 ._-]+$/']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $vendorAuthenticationId = $credentials->sub;
                /* To get the order details */
                $orderDetail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
                if ($orderDetail) {
                    if ($vendorAuthenticationId <> $orderDetail->vendor_authentication_id) {
                        return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Token.');
                    }
                    Orders::AccpetOrder($input['order_id'], $input['reason'], $vendorAuthenticationId);

                    return ResponseBuilder::responseResult($this->successStatus, 'Order Accepted Successfully');
                }
                return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Order');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');

        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function rejectOrder(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['reason'] = isset($input['reason']) ? trim($input['reason']) : '';
            $input['language'] = (isset($input['language']) && $input['language'] == 'ar') ? 'ar' : 'en';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'order_id' => ['required', 'numeric', 'exists:orders,id'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'reason' => ['regex:/^[a-zA-Z0-9 ._-]+$/']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $vendorAuthenticationId = $credentials->sub;
                /* To get the order details */
                $orderDetail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
                if ($orderDetail) {
                    if ($vendorAuthenticationId <> $orderDetail->vendor_authentication_id) {
                        return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Token.');
                    }
                    Orders::RejectOrder($input['order_id'], $input['reason'], $vendorAuthenticationId);

                    return ResponseBuilder::responseResult($this->successStatus, 'Order rejected successfully');
                }
                return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Order');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function readyforDispatch(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['order_id'] = isset($input['order_id']) ? trim($input['order_id']) : '';
            $input['vendor_id'] = isset($input['vendor_id']) ? trim($input['vendor_id']) : '';
            $input['reason'] = isset($input['reason']) ? trim($input['reason']) : '';
            $input['language'] = (isset($input['language']) && $input['language'] == 'ar') ? 'ar' : 'en';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'order_id' => ['required', 'numeric', 'exists:orders,id'],
                'vendor_id' => ['required', 'numeric', 'exists:vendors,id'],
                'reason' => ['regex:/^[a-zA-Z0-9 ._-]+$/']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }
            /* To check the token is valid or not */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $vendorAuthenticationId = $credentials->sub;
                /* To get the order details */
                $orderDetail = Orders::OrderDetail($input['order_id'], $input['vendor_id']);
                if ($orderDetail) {
                    if ($vendorAuthenticationId <> $orderDetail->vendor_authentication_id) {
                        return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Token.');
                    }
                    Orders::ReadyforDispatch($input['order_id'], $input['reason'],$vendorAuthenticationId);

                    return ResponseBuilder::responseResult($this->successStatus, 'Order is ready for delivery.');
                }
                return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Order');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');

        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function OrderStatus(Request $request)
    {
        try {
            $input = $request->all();
            $vendorId = $input['vendor_id'];
            $status_id = $input['status_id'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $order_list['new'] = Orders::orderStatus($vendorId,1);
                $order_list['delivered'] = Orders::orderStatus($vendorId,6);
                $order_list['total'] = Orders::orderStatus($vendorId,'');

                return ResponseBuilder::responseResult($this->successStatus, 'status List',array($order_list));
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function AllOrderStatus(Request $request)
    {
        try {
            $input = $request->all();
            $vendorId = $input['vendor_id'];
            $status_id = $input['status_id'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $order_list['new'] = Orders::allOrderStatus($vendorId,1);
                $order_list['processing'] = Orders::allOrderStatus($vendorId,2);
                $order_list['drverAccept'] = Orders::allOrderStatus($vendorId,3);
                $order_list['readyForPickUp'] = Orders::allOrderStatus($vendorId,4);
                $order_list['pickUp'] = Orders::allOrderStatus($vendorId,5);
                $order_list['delivered'] = Orders::allOrderStatus($vendorId,6);
                $order_list['reject'] = Orders::allOrderStatus($vendorId,7);

                return ResponseBuilder::responseResult($this->successStatus, 'status List',array($order_list));
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function Revenue(Request $request)
    {
        try {
            $input = $request->all();
            $vendorId = $input['vendor_id'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $revenue['cash'] = Orders::getRevenue($vendorId,array(1),$input['from_date'],$input['to_date']);
                $revenue['card'] = Orders::getRevenue($vendorId,array(2,3,4),$input['from_date'],$input['to_date']);
                $revenue['total'] = Orders::getRevenue($vendorId,'',$input['from_date'],$input['to_date']);
                $revenue['completed_orders'] = Orders::getRevenueStatus($vendorId,6, $input['from_date'],$input['to_date']);
                $revenue['total_orders'] = Orders::getRevenueStatus($vendorId,'', $input['from_date'],$input['to_date']);

                return ResponseBuilder::responseResult($this->successStatus, 'Total Revenue',$revenue);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function RevenueList(Request $request)
    {
        try {
            $input = $request->all();
            $vendorId = $input['vendor_id'];//$pauyment_mode = $input['payment_mode'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $revenue['cash'] = Orders::getRevenueList($vendorId,array(1),$input['from_date']);
                $revenue['card'] = Orders::getRevenueList($vendorId,array(2,3,4), $input['from_date']);

                return ResponseBuilder::responseResult($this->successStatus, 'Total Revenue',$revenue);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

}
