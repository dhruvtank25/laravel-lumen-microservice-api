<?php

namespace App\Http\V_1_0_0\Migration\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;

use App\Http\V_1_0_0\Migration\Models\Vendors;
use App\Http\V_1_0_0\Migration\Models\Authentications;
use App\Http\V_1_0_0\Migration\Models\Authentication_devices;
use App\Http\V_1_0_0\Migration\Models\Vendor_categories;

use App\Helpers\ResponseBuilder;

//use Cache;

class ImportVendor extends Controller
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
    public function importVendor(Request $request)
    {
        try {
            error_reporting(E_ALL);
            set_time_limit(0);
            $input = $request->all();
            $rules = array(
                'vendorExcel' => 'required',
            );
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator);
            }
            $vendorExcel = (isset($input['vendorExcel']) && !empty($input['vendorExcel']))?$input['vendorExcel']:'';

            if (!empty($vendorExcel)) {
                $v = 0;
                $imageName = $_FILES['vendorExcel']['name'];
                $request->file('vendorExcel')->move(
                    base_path() . '/public/assets/sample_format_excel/upload/', $imageName
                );
                $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load(base_path().'/public/assets/sample_format_excel/upload/'.$_FILES['vendorExcel']['name']);
                $objWorksheet  = $objPHPExcel->getActiveSheet();
                $highestRow    = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
                $headingsArray = $headingsArray[1];
                $r = -1;
                $namedDataArray = $both_exists = $email_exists = $phone_exists = $other_exist = array();
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
                    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] != '')) {
                        ++$r;
                        foreach($headingsArray as $columnKey => $columnHeading) {
                            $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                        }
                    }
                }
                if (count($namedDataArray) > 0) {
                    foreach ($namedDataArray as $key => $row){
                        /* To check the email id is already exist or not */
                        //$emailExist = Vendors::checkEmailAlreadyExistOrNot($row['EMAILID']);
                        //$input['phone'] = is_numeric($row['MOBILENO'])?$row['MOBILENO']:0;
                        //$phoneExist = Vendors::checkMobileNoAlreadyExistOrNot($input['phone']);
                        //if (empty($emailExist) && empty($phoneExist)) {
                            /* To insert the data in authentication table */
                            $input['akeed_user_id'] = $row['ID'];
                            $input['email'] = $row['EMAILID'];
                            $input['phone'] = is_numeric($row['MOBILENO'])?$row['MOBILENO']:0;
                            $input['firstname'] = $row['FIRSTNAME'];
                            $input['firstname_ar'] = $row['ARFIRSTNAME'];
                            $input['lastname'] = ($row['LASTNAME'] == 'NULL')?null:$row['LASTNAME'];
                            $input['lastname_ar'] = ($row['ARLASTNAME'] == 'NULL')?null:$row['ARLASTNAME'];
                            $input['country_code'] = '+95';
                            $input['plain_password'] = $row['PASSWORD'];
                            $input['password'] = Hash::make($row['PASSWORD']);
                            $input['role'] = 2;
                            $input['created_at'] = date('Y-m-d H:i:s',strtotime(str_replace('/','-',$row['CREATEDON'])));
                            $input['updated_at'] = date('Y-m-d H:i:s',strtotime(str_replace('/','-',$row['UPDATEDON'])));
                            /* To check the vendor is already exist or not */
                            $checkAuthentication = Authentications::checkAuthenticationVendor($row['ID']);
                            if (isset($checkAuthentication->id) && !empty($checkAuthentication->id)) {
                                $authentication_id = Authentications::update_Authentication($input, $checkAuthentication->id);
                            } else {
                                $authentication_id = Authentications::Insert_Authentication($input);
                            }
                            /* To insert the customer table */
                            $input['authentication_id'] = $authentication_id;
                            $input['shop_name_en'] = $input['firstname'].' '.$input['lastname'];
                            $input['shop_name_ar'] = $row['ARFIRSTNAME'].' '.$input['lastname_ar'];
                            $input['tag_line_en'] = $row['TAGLINE'];
                            $input['tag_line_ar'] = $row['ARTAGLINE'];
                            $input['alternate_mobile_country_code'] = '+95';
                            $input['alternate_mobile'] = !empty($row['ALTERNATEMOBILE'])?$row['ALTERNATEMOBILE']:null;
                            $input['address_1'] = $row['ADDRESS1'];
                            $input['address_2'] = $row['ADDRESS2'];
                            $input['landmark'] = $row['LANDMARK'];
                            $input['latitude'] = $row['LATITUDE'];
                            $input['longitude'] = $row['LONGITUDE'];
                            $input['logo'] = $row['LOGO'];
                            $input['web_logo'] = $row['WEBLOGO'];
                            $input['serving_distance'] = (isset($row['servingdistance']) && !empty($row['servingdistance']))?$row['servingdistance']:15;
                            $input['gender'] = (($row['GENDER'] != 'NULL') && !empty($row['GENDER']))?$row['GENDER']:'Male';
                            $input['salary'] = $row['SALARY'];
                            /* To check the restaurant category is available or not */
                            $categoryDetail = Vendor_categories::getCategoryDetailById($row['servicecategoryid']);
                            if (empty($categoryDetail)) {
                                /* To insert the category name */
                                $newCategory = Vendor_categories::InsertVendorCategory($row['RESTURANTSCATEGORY'],$row['servicecategoryid']);
                                $categoryId = $newCategory->id;
                                $categoryNameEn = $newCategory->name_en;
                                $categoryNameAr = $newCategory->name_ar;
                            } else {
                                $categoryId = $categoryDetail->id;
                                $categoryNameEn = $categoryDetail->name_en;
                                $categoryNameAr = $categoryDetail->name_ar;
                            }
                            $input['vendor_category_id'] = $categoryId;
                            $input['vendor_category_en'] = $categoryNameEn;
                            $input['vendor_category_ar'] = $categoryNameAr;
                            $input['OpeningTime'] = $row['OPENINGTIME'];
                            $input['OpeningTime2'] = $row['OPENINGTIME2'];
                            $input['commission'] = (!empty($row['COMMISSSION']) && is_numeric($row['COMMISSSION']))?$row['COMMISSSION']:0;
                            $input['delivery_charge'] = (!empty($row['DELIVERYCHRGE']) && is_numeric($row['DELIVERYCHRGE']))?$row['DELIVERYCHRGE']:0;
                            $input['service_charge'] = (!empty($row['SERVICECHRGE']) && is_numeric($row['SERVICECHRGE']))?$row['SERVICECHRGE']:0;
                            $input['municipal_tax'] = (!empty($row['MUNICIPALITYTAX']) && is_numeric($row['MUNICIPALITYTAX']))?$row['MUNICIPALITYTAX']:0;
                            $input['is_akeed_delivering'] = $row['ISAKEEDDELIVERING'];
                            $input['is_open'] = strval($row['ISOPEN']);
                            $input['prepration_time'] = (isset($row['PREPARATIONTIME']) && is_numeric($row['PREPARATIONTIME']))?$row['PREPARATIONTIME']:15;
                            $input['akeed_percentage'] = (isset($row['AKEEDPERCENTAGE']) && is_numeric($row['AKEEDPERCENTAGE']))?$row['AKEEDPERCENTAGE']:0;
                            $input['discount_active_from'] = $input['discount_active_to'] = '';
                            if (!empty($row['DISCOUNTACTIVEFROM']) && !empty($row['DISCOUNTACTIVETO'])) {
                                $input['discount_active_from'] = date('Y-m-d',strtotime($row['DISCOUNTACTIVEFROM']));
                                $input['discount_active_to'] = date('Y-m-d',strtotime($row['DISCOUNTACTIVETO']));
                            }
                            $input['min_order_discount'] = $row['MINORDERDISCOUNT'];
                            $input['discount_amount'] = (isset($row['DISCOUNTAMOUNT']) && is_numeric($row['DISCOUNTAMOUNT']))?$row['DISCOUNTAMOUNT']:0;
                            $input['discount_percentage'] = (isset($row['DISCOUNTPERCENTAGE']) && is_numeric($row['DISCOUNTPERCENTAGE']))?$row['DISCOUNTPERCENTAGE']:0;
                            $input['language'] = (isset($row['LANGUAGE']) && empty($row['LANGUAGE']))?'':'EN';
                            $input['open_close_flags'] = strval($row['OpenCloseFlage']);
                            $input['status'] = strval($row['ISACTIVE']);
                            $input['verified'] = ($row['ISVERIFIED'] == 1)?'1':'0';
                            /* To check the vendor is already added or not */
                            $checkVendor = Vendors::getVendorDetail($row['ID']);
                            if (isset($checkVendor->id) && !empty($checkVendor->id)) {
                                $vendor_id = Vendors::Update_vendor($input, $checkVendor->id);
                            } else {
                                $vendor_id = Vendors::Insert_vendor($input);
                                $input['fcm_token'] = $row['DEVICEID'];
                                $input['imei_number'] = $row['IMEINUMBER'];
                                $input['otp'] = $row['OTP'];
                                /* To insert the authentication device table */
                                $input['vendor_id'] = $vendor_id;
                                $authentication_device_id = Authentication_devices::Insert_devices($input);
                            }
                            $v++;
                        /*} else {
                            if (!empty($emailExist) && !empty($phoneExist)) {
                                $both_exists['vendor_id'][] = $row['ID'];
                            } elseif (!empty($emailExist)) {
                                $email_exists['vendor_id'][] = $row['ID'];
                            } elseif (!empty($phoneExist)) {
                                $phone_exists['vendor_id'][] = $row['ID'];
                            } else {
                                $other_exist['vendor_id'][] = $row['ID'];
                            }
                            
                        }*/
                    }
                }
                echo 'Vendor Added Successfully.<br>Total Count = '.count($namedDataArray).'<br>Inserted Vendor Count = '.$v.'<br>';print_r($both_exists);echo '<br>';print_r($email_exists);echo '<br>';print_r($phone_exists);echo '<br>';print_r($other_exist);exit;
            } else {
                echo 'No Vendor Found';exit;
            }
        } catch (Exception $e) {
            print_r($e->getMessage());die;
        }
    }
}
