<?php

namespace App\Http\V_1_0_0\MenuItems\Models;

use Illuminate\Database\Eloquent\Model;

class Import_error_logs extends Model
{
    /* To get the add-on category details */
    public static function insertLogs($vendor_id,$f_name,$err_logs)
    {
        $logs = json_encode($err_logs);
        $err = new Import_error_logs;
        $err->file_name = $f_name;
        $err->vendor_id = $vendor_id;
        $err->error_logs =  $logs;
        $err->save();
    }

    
    
}
