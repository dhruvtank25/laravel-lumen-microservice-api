<?php

namespace App\Http\V_1_0_0\GlobalSettings\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;

use Firebase\JWT\JWT;
use App\Http\V_1_0_0\GlobalSettings\Models\General_settings;
use App\Helpers\ResponseBuilder;

//use Cache;

class SettingController extends Controller
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
    public function index(Request $request)
    {

        try {
            $input = $request->all();
           // $code=$input['code'];
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
              $setting_list['email'] = General_settings::getSetting('email');
                $setting_list['mobile'] = General_settings::getSetting('mobile');
                $setting_list['address'] = General_settings::getSetting('address');
                $setting_list['website'] = General_settings::getSetting('website');

                return ResponseBuilder::responseResult($this->successStatus, 'Lists',$setting_list);
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
