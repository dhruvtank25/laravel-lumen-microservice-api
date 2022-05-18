<?php

namespace App\Http\V_1_0_0\Authentications\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Http\V_1_0_0\Authentications\Models\Authentications;
use App\Http\V_1_0_0\Authentications\Models\Authentication_devices;
use App\Helpers\ResponseBuilder;

//use Cache;

class AuthenticationController extends Controller
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
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function Login(Request $request)
    {
        try {
            $input = $request->all();
            //echo Input::get('user_id');exit;
            $input['user_id'] = isset($input['user_id']) ? trim($input['user_id']) : '';
            $input['device_id'] = isset($input['device_id']) ? trim($input['device_id']) : '';
            $input['device_name'] = isset($input['device_name']) ? trim($input['device_name']) : '';
            $input['device_version'] = isset($input['device_version']) ? trim($input['device_version']) : '';
            $input['app_version'] = isset($input['app_version']) ? trim($input['app_version']) : '';
            $input['gcm_id'] = isset($input['gcm_id']) ? trim($input['gcm_id']) : '';
            $input['fcm_token'] = isset($input['fcm_token']) ? trim($input['fcm_token']) : '';
            $input['device_type'] = isset($input['device_type']) ? trim($input['device_type']) : '';
            $input['imei_number'] = isset($input['imei_number']) ? trim($input['imei_number']) : '';
            $input['mobile_number'] = $input['email'] = '';
            /* To check the given input is email or mobile number */
            if (is_numeric($input['user_id'])) {
                $input['mobile_number'] = $input['user_id'];
                $input['login_type'] = 1;
            } else {
                $input['email'] = $input['user_id'];
                $input['login_type'] = 2;
            }
            $rules = [
                'user_type' => ['required', 'in:2'],
                'mobile_number' => ['required_if:login_type,1', 'numeric', 'digits_between:7,10', 'exists:authentications,phone'],
                'email' => ['required_if:login_type,2', 'email', 'max:150', 'exists:authentications,email'],
                'password' => ['required', 'min:6', 'max:16']
            ];
            $validator = app('validator')->make($input, $rules);

            $error = $result = array();
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(', \n ', $error);
                return ResponseBuilder::responseResult($this->failureStatus, $errors);
            }//echo Hash::make($input['password']);die;
            /* To check the mobile is valid or not */
            $userDetail = Authentications::getLoginDetails($input['user_type'], $input['email'], $input['mobile_number']);
            //print_r($userDetail);
            if (!empty($userDetail)) {
                if ($userDetail->IsActive == '1') {
                    if (Hash::check($input['password'], $userDetail->password)) {
                        $input['akeed_user_id'] = $userDetail->akeed_user_id;
                        $input['authentication_id'] = $userDetail->authentication_id;
                        $input['status'] = '1';
                        $authentication_device_id = Authentication_devices::Insert_devices($input);
                        $data = $userDetail;
                        $data['Type']="Vendor";
                        $data['authentication_device_id'] = $authentication_device_id;
                        //$data['token'] = JWTAuth::fromUser($userDetail);
                        $data['token'] = $this->jwt($userDetail);
                        return ResponseBuilder::responseResult($this->successStatus, 'Logged-in Successfully', $data);
                    }
                    return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Password');
                }
                return ResponseBuilder::responseResult($this->failureStatus, 'We are not seeing you as an active user please contact to our admin');
            }
            return ResponseBuilder::responseResult($this->failureStatus, 'Invalid Credential');
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['authentication_device_id'] = isset($input['authentication_device_id']) ? trim($input['authentication_device_id']) : '';
            $input['logout_from_all'] = isset($input['logout_from_all']) ? trim($input['logout_from_all']) : '';
            /* To check the given input is email or mobile number */

            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/',],
                'authentication_device_id' => ['required', 'numeric'],
                'logout_from_all' => ['required', 'boolean'],
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
                $input['authentication_id'] = $credentials->sub;
                $userDetail = Authentications::logout($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Logged-out Successfully',$userDetail);
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

    public function Change_password(Request $request)
    {
        try {
            $input = $request->all();

            $input['password'] =  $input['password'];
            $input['ConfirmPassword'] = isset($input['ConfirmPassword']) ? trim($input['ConfirmPassword']) : '';
            $input['authentication_id'] = isset($input['authentication_id']) ? trim($input['authentication_id']) : '';

            /* To check the given input is email or mobile number */
            $rules = [
                'authentication_id' => ['required', 'numeric'],
                'password' => ['required', 'min:8', 'max:16'],
                'ConfirmPassword' => ['required', 'min:8', 'max:16']
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

            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);

            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                if ($input['password']== $input['ConfirmPassword']){
                    $userDetail = Authentications::changePassword($input['authentication_id'], $input['password']);
                    return ResponseBuilder::responseResult($this->successStatus, 'password changed Successfully',$userDetail);
                } else {
                    return ResponseBuilder::responseResult($this->failureStatus, 'password and Confirm password should be same.');
                }
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
    protected function jwt($user) {
        $payload = [
            'iss' => "akeed-lumen-jwt", // Issuer of the token
            'sub' => $user->authentication_id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + (365*24*60*60) // Expiration time (1 year)
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public function checkAuthToken (Request $request)
    {
        $input = $request->all();
        $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
        if ($credentials) {
            $input['authentication_id'] = $credentials->sub;
            return ResponseBuilder::responseResult($this->successStatus);
        } else {
            return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
        }
    }
}
