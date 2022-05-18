<?php

namespace App\Http\V_1_0_0\AddOnCategory\Controllers;

use App\Http\Controller;
use Illuminate\Http\Request;
use Validator;
use Firebase\JWT\JWT;
use App\Helpers\ResponseBuilder;

use App\Http\V_1_0_0\AddOnCategory\Models\Addon_categories;
use App\Http\V_1_0_0\AddOnCategory\Models\Addon_menu_item_mappings;

//use Cache;

class AddOnCategoryController extends Controller
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
        
    }

    /**
     * add-on category list api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
                $addon_category_list = Addon_categories::getAddOnCategoriesList();
                return ResponseBuilder::responseResult($this->successStatus, 'Addon Category List has been fetched successfully', $addon_category_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To get the list of add on categories(It's showing in menu items) */
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
                $addon_category_list = Addon_categories::getAddOnCategories();
                return ResponseBuilder::responseResult($this->successStatus, 'Addon Category List has been fetched successfully', $addon_category_list);
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* This API for show the details of category in edit and view page of admin panel */
    public function details(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['id'] = isset($input['id']) ? trim($input['id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:addon_categories,id'],
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
                $addOnCategoryDetail = Addon_categories::getAddOnCategoryDetails($input['id']);
                return ResponseBuilder::responseResult($this->successStatus, 'Add On Category Details has been fetched successfully', $addOnCategoryDetail);

            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To store the new category details */
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[A-Za-z0-9_.,!@#$%^*&()\-? ]+$/'],
                'name_en' => ['required', 'max:32', 'regex:/^[a-zA-Z0-9 ._-]+$/'],
                'name_ar' => ['max:32'],
                'type' => ['required', 'in:Exactly,Non-Exactly'],
                'mandatory' => ['required', 'boolean', 'numeric', 'min:0', 'max:1'],
                'no_of_mandatory' => ['required', 'numeric', 'min:0', 'max:10'],
                'sort_order' => ['required', 'numeric', 'min:0', 'max:10'],
                'status' => ['required', 'min:0', 'max:1', 'boolean']
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
                $authentication_id = Addon_categories::Insert_addon_category($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Add-On Category Saved Successfully');
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* To update the category details */
    public function update(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'token' => ['required', 'regex:/^[A-Za-z0-9_.,!@#$%^*&()\-? ]+$/'],
                'id' => ['required', 'numeric', 'exists:addon_categories,id'],
                'name_en' => ['required', 'max:32', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'name_ar' => ['max:32'],
                'type' => ['required', 'in:Exactly,Non-Exactly'],
                'mandatory' => ['required', 'boolean', 'numeric', 'min:0', 'max:1'],
                'no_of_mandatory' => ['required', 'numeric', 'min:0', 'max:10'],
                'sort_order' => ['required', 'numeric', 'min:0', 'max:10'],
                'status' => ['required', 'min:0', 'max:1', 'boolean']
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
                Addon_categories::update_addon_category($input);
                return ResponseBuilder::responseResult($this->successStatus, 'Add-On Category updated successfully');
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }

    /* to update the category status using id and status field */
    public function updateStatus(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['id'] = isset($input['id']) ? trim($input['id']) : '';
            $input['status'] = isset($input['status']) ? trim($input['status']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:addon_categories,id'],
                'status' => ['required', 'boolean', 'numeric'],
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
                Addon_categories::updateStatus($input['id'], $input['status']);
                return ResponseBuilder::responseResult($this->successStatus, 'Add-On Category status changed successfully');
            } else {
                return ResponseBuilder::responseResult($this->failureStatus, 'Something went wrong.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseBuilder::responseResult($this->failureStatus, $e->getMessage());
        }
    }
    /* To delete the category. If the category not used the menu_items. */
    public function deleteCategory(Request $request)
    {
        try {
            $input = $request->all();
            $input['token'] = isset($input['token']) ? trim($input['token']) : '';
            $input['id'] = isset($input['id']) ? trim($input['id']) : '';
            $rules = [
                'token' => ['required', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'id' => ['required', 'numeric', 'exists:addon_categories,id'],
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
                /* to check this add-on category is already exist on menu items or not */
                $check_category_exist = Addon_menu_item_mappings::checkAddOnCategoryExist($input['id']);
                if (!$check_category_exist) {
                    Addon_categories::deleteAddOnCategory($input['id']);
                    return ResponseBuilder::responseResult($this->successStatus, 'Add-on category has been deleted successfully');
                } else {
                    return ResponseBuilder::responseResult($this->failureStatus, 'This add-on category already exist in menu item');
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
}
