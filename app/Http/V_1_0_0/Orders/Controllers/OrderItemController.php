<?php

namespace App\Http\V_1_0_0\Orders\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\Orders\Models\Order_items;
use App\Http\V_1_0_0\Orders\Models\Order_addons;
use App\Helpers\ResponseBuilder;

//use Cache;

class OrderItemController extends Controller
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
                $order_items = Order_items::orderItem($input['order_id']);
                $order_item_list = array();
                if (!empty($order_items)) {
                    $i = 0;
                    foreach ($order_items as $item) {
                        $order_item_list[$i]['item_id'] = $item->item_id;
                        $order_item_list[$i]['order_id'] = $item->order_id;
                        $order_item_list[$i]['ITEMNAME'] = $item->ITEMNAME;
                        $order_item_list[$i]['price'] = $item->price;
                        $order_item_list[$i]['type'] = $item->type;
                        $order_item_list[$i]['TotalPrice'] = $item->TotalPrice;
                        $order_item_list[$i]['is_addon'] = $item->is_addon;
                        $order_item_list[$i]['addons'] = $item->addons;
                        $order_item_list[$i]['addon_subtotal'] = $item->addon_subtotal;
                        $order_item_list[$i]['MENUCATEGORYNAME'] = $item->MENUCATEGORYNAME;
                        $order_item_list[$i]['bar_code'] = $item->bar_code;
                        $order_item_list[$i]['sku_code'] = $item->sku_code;
                        $order_item_list[$i]['LANGUAGE'] = $item->LANGUAGE;
                        $order_item_list[$i]['quantity'] = $item->quantity;
                        $order_item_list[$i]['Status'] = $item->Status;
                        $order_item_list[$i]['PaymentMode'] = $item->PaymentMode;
                        $order_item_list[$i]['DeliveryCharge'] = $item->DeliveryCharge;
                        $order_item_list[$i]['TAX'] = $item->TAX;
                        $order_item_list[$i]['ServiceTax'] = $item->ServiceTax;
                        $order_item_list[$i]['vendor_discount_amount'] = $item->vendor_discount_amount;
                        $order_item_list[$i]['promo_code'] = $item->promo_code;
                        $addon_detail = array();
                        if (!empty($item->addons)) {
                            $addonList = Order_addons::getOrderAddOn($item->item_id);
                            $addon_detail = $addonList;
                            /*$a = 0;
                            if (!empty($addonList)) {
                                foreach ($addonList as $addon) {
                                    $addon_detail[$a]['id'] = $addon->id;
                                    $a++;
                                }
                            }*/
                        }
                        $order_item_list[$i]['addon_detail'] = $addon_detail;
                        $i++;
                    }
                }
                return ResponseBuilder::responseResult($this->successStatus, 'Order Items', $order_item_list);
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

}
