<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\V_1_0_0\Orders\Controllers\AcceptOrderController;
use App\Http\V_1_0_0\Orders\Controllers\PickUpOrderController;
use App\Http\V_1_0_0\Orders\Controllers\DeliverOrderController;
use App\Http\V_1_0_0\Orders\Controllers\NewOrderListController;
use App\Http\V_1_0_0\Orders\Controllers\PastOrderListController;
use App\Http\V_1_0_0\Orders\Controllers\CurrentOrderListController;

class OrdersTest extends TestCase
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNewOrders_list()
    {
        $request=new Request();
        $new_orders = new NewOrderListController();
        $request['driver_id']=1;
        $result=$new_orders->NewOrderList($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

    public function testCurrentOrders_list()
    {
        $request=new Request();
        $pickup_orders = new CurrentOrderListController();
        $request['driver_id']=1;
        $result=$pickup_orders->CurrentOrderList($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

    public function testPastOrders_list()
    {
        $request=new Request();
        $past_orders = new PastOrderListController();
        $request['driver_id']=1;
        $result=$past_orders->PastOrderList($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

    public function testAcceptOrders()
    {
        $request=new Request();
        $accepts_orders = new AcceptOrderController();
        $request['order_id']=2;
        $request['driver_id']=1;
        $result=$accepts_orders->AcceptOrder($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }


    public function testPickUpOrder()
    {
        $request=new Request();
        $deliver_orders = new PickUpOrderController();
        $request['driver_id']=1;
        $request['order_id']=7;
        $result=$deliver_orders->PickUpOrder($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

    public function testDeliverOrder()
    {
        $request=new Request();
        $deliver_orders = new DeliverOrderController();

        $request['order_id']=3;
        $request['driver_id']=1;
        $result=$deliver_orders->DeliveredOrder($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

}
