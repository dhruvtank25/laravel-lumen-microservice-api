<?php

namespace App\Http\V_1_0_0\Tags\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use Firebase\JWT\JWT;

use App\Http\V_1_0_0\Tags\Models\Tag_masters;
use App\Helpers\ResponseBuilder;

//use Cache;

class TagController extends Controller
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
     * To show on all the vendors in vendor management
     */
    public function dropDownList(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/']
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
            /* To check the token */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $tagsList = Tag_masters::getTagMastersList();
                return ResponseBuilder::responseResult($this->successStatus, 'Tags List has been fetched successfully', $tagsList);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    public function details(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'primary_tag' => ['required']
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
            /* To check the token */
            $credentials = JWT::decode($input['token'], env('JWT_SECRET'), ['HS256']);
            if ($credentials) {
                $input['authentication_id'] = $credentials->sub;
                $tagDetail = Tag_masters::getTagMastersDetail($input);

                return ResponseBuilder::responseResult($this->successStatus, 'Tag Detail', $tagDetail);
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
