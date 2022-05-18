<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\V_1_0_0\Authentications\Controllers\AuthenticationController;

class LoginTest extends TestCase
{

    public $successStatus = 200;
    public $failureStatus = 400;
    public $validationErrStatus = 402;

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
    public function testDriverLogin()
    {
        $request=new Request();

        $login = new AuthenticationController();

        $request['user_id']="sonam.shrivastava@razorse.com";
        $request['password'] = "123456";
        $request['device_type'] ="android";
        $request['user_type'] =3;
        $result=$login->login($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }

    public function testLogout()
    {
        $request=new Request();

        $logout = new AuthenticationController();

        $request['authentication_id']=15;
        $request['authentication_device_id'] = 446;
        $request['logout_from_all'] =0;
        $result=$logout->Logout($request);
        $output=json_decode($result->content(), TRUE);
        $this->assertEquals(200,$output["http_code"],$output["message"]);
    }


}
