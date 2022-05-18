<?php

namespace App\Http\V_1_0_0\Migration\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Validator;

use App\Http\V_1_0_0\Migration\Models\Vendors;

use App\Helpers\ResponseBuilder;

//use Cache;

class ImportVendorTiming extends Controller
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
    public function ImportVendorTiming(Request $request)
    {
        try {
            error_reporting(E_ALL);
            set_time_limit(0);
            $input = $request->all();
            $rules = array(
                'vendorTimingExcel' => 'required',
            );
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator);
            }
            $vendorTimingExcel = (isset($input['vendorTimingExcel']) && !empty($input['vendorTimingExcel']))?$input['vendorTimingExcel']:'';

            if (!empty($vendorTimingExcel)) {
                $imageName = $_FILES['vendorTimingExcel']['name'];
                $request->file('vendorTimingExcel')->move(
                    base_path() . '/public/assets/sample_format_excel/upload/', $imageName
                );
                $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load(base_path().'/public/assets/sample_format_excel/upload/'.$_FILES['vendorTimingExcel']['name']);
                $objWorksheet  = $objPHPExcel->getActiveSheet();
                $highestRow    = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
                $headingsArray = $headingsArray[1];
                $r = -1;
                $namedDataArray = array();
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
                    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] != '')) {
                        ++$r;
                        foreach($headingsArray as $columnKey => $columnHeading) {
                            $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                        }
                    }
                }
                if(count($namedDataArray)>0) {
                    foreach ($namedDataArray as $key => $row){
                        /* To check the vendor id is available or not */
                        $vendorDetail = Vendors::getVendorDetail($row['VENDORID']);
                        if (!empty($vendorDetail)) {
                            $from_time1 = $to_time1 = $from_time2 = $to_time2 = $from_time3 = $to_time3 = $from_time4 = $to_time4 = null;
                            /* To check the first time is available or not */
                            $time_1 = trim(trim($row['TIME1']),'-');
                            if (!empty($time_1)) {
                                $time1 = explode('-',str_replace('00:','12:',$time_1));
                                if (!empty($time1)) {
                                    $from_time1 = (isset($time1[0]) && !empty($time1[0]))?date('H:i:s',strtotime($time1[0])):null;
                                    $to_time1 = (isset($time1[1]) && !empty($time1[1]))?date('H:i:s',strtotime($time1[1])):null;
                                }
                            }
                            /* To check the second time is available or not */
                            $time_2 = trim(trim($row['TIME2']),'-');
                            if (!empty($time_2)) {
                                $time2 = explode('-',str_replace('00:','12:',$time_2));
                                if (!empty($time2)) {
                                    $from_time2 = (isset($time2[0]) && !empty($time2[0]))?date('H:i:s',strtotime($time2[0])):null;
                                    $to_time2 = (isset($time2[1]) && !empty($time2[1]))?date('H:i:s',strtotime($time2[1])):null;
                                }
                            }
                            /* To check the third time is available or not */
                            $time_3 = trim(trim($row['TIME3']),'-');
                            if (!empty($time_3)) {
                                $time3 = explode('-',str_replace('00:','12:',$time_3));
                                if (!empty($time3)) {
                                    $from_time3 = (isset($time3[0]) && !empty($time3[0]))?date('H:i:s',strtotime($time3[0])):null;
                                    $to_time3 = (isset($time3[1]) && !empty($time3[1]))?date('H:i:s',strtotime($time3[1])):null;
                                }
                            }
                            /* To check the fourth time is available or not */
                            $time_4 = trim(trim($row['TIME4']),'-');
                            if (!empty($time_4)) {
                                $time4 = explode('-',str_replace('00:','12:',$time_4));
                                if (!empty($time4)) {
                                    $from_time4 = (isset($time4[0]) && !empty($time4[0]))?date('H:i:s',strtotime($time4[0])):null;
                                    $to_time4 = (isset($time4[1]) && !empty($time4[1]))?date('H:i:s',strtotime($time4[1])):null;
                                }
                            }
                            if ($row['DAY'] == 'Sun') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->sunday_from_time1 = $from_time1;
                                $updateVendor->sunday_to_time1 = $to_time1;
                                $updateVendor->sunday_from_time2 = $from_time2;
                                $updateVendor->sunday_to_time2 = $to_time2;
                                $updateVendor->sunday_from_time3 = $from_time3;
                                $updateVendor->sunday_to_time3 = $to_time3;
                                $updateVendor->sunday_from_time4 = $from_time4;
                                $updateVendor->sunday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Mon') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->monday_from_time1 = $from_time1;
                                $updateVendor->monday_to_time1 = $to_time1;
                                $updateVendor->monday_from_time2 = $from_time2;
                                $updateVendor->monday_to_time2 = $to_time2;
                                $updateVendor->monday_from_time3 = $from_time3;
                                $updateVendor->monday_to_time3 = $to_time3;
                                $updateVendor->monday_from_time4 = $from_time4;
                                $updateVendor->monday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Tue') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->tuesday_from_time1 = $from_time1;
                                $updateVendor->tuesday_to_time1 = $to_time1;
                                $updateVendor->tuesday_from_time2 = $from_time2;
                                $updateVendor->tuesday_to_time2 = $to_time2;
                                $updateVendor->tuesday_from_time3 = $from_time3;
                                $updateVendor->tuesday_to_time3 = $to_time3;
                                $updateVendor->tuesday_from_time4 = $from_time4;
                                $updateVendor->tuesday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Wed') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->wednesday_from_time1 = $from_time1;
                                $updateVendor->wednesday_to_time1 = $to_time1;
                                $updateVendor->wednesday_from_time2 = $from_time2;
                                $updateVendor->wednesday_to_time2 = $to_time2;
                                $updateVendor->wednesday_from_time3 = $from_time3;
                                $updateVendor->wednesday_to_time3 = $to_time3;
                                $updateVendor->wednesday_from_time4 = $from_time4;
                                $updateVendor->wednesday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Thu') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->thursday_from_time1 = $from_time1;
                                $updateVendor->thursday_to_time1 = $to_time1;
                                $updateVendor->thursday_from_time2 = $from_time2;
                                $updateVendor->thursday_to_time2 = $to_time2;
                                $updateVendor->thursday_from_time3 = $from_time3;
                                $updateVendor->thursday_to_time3 = $to_time3;
                                $updateVendor->thursday_from_time4 = $from_time4;
                                $updateVendor->thursday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Fri') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->friday_from_time1 = $from_time1;
                                $updateVendor->friday_to_time1 = $to_time1;
                                $updateVendor->friday_from_time2 = $from_time2;
                                $updateVendor->friday_to_time2 = $to_time2;
                                $updateVendor->friday_from_time3 = $from_time3;
                                $updateVendor->friday_to_time3 = $to_time3;
                                $updateVendor->friday_from_time4 = $from_time4;
                                $updateVendor->friday_to_time4 = $to_time4;
                                $updateVendor->save();
                            } elseif ($row['DAY'] == 'Sat') {
                                /* To update the vendor timings */
                                $updateVendor = Vendors::find($vendorDetail->id);
                                $updateVendor->saturday_from_time1 = $from_time1;
                                $updateVendor->saturday_to_time1 = $to_time1;
                                $updateVendor->saturday_from_time2 = $from_time2;
                                $updateVendor->saturday_to_time2 = $to_time2;
                                $updateVendor->saturday_from_time3 = $from_time3;
                                $updateVendor->saturday_to_time3 = $to_time3;
                                $updateVendor->saturday_from_time4 = $from_time4;
                                $updateVendor->saturday_to_time4 = $to_time4;
                                $updateVendor->save();
                            }
                        }
                    }
                }
            }
            echo 'Added Successfully';exit;
        } catch (Exception $e) {
            print_r($e->getMessage());die;
        }
    }
}
