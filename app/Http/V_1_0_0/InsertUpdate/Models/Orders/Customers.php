<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    public static function getCustomerDetails($customer_id)
    {
        return Customers::select('id', 'authentication_id', 'firstname', 'lastname', 'phone', 'email')->where('akeed_customer_id', '=', $customer_id)->first();
    }

    /* To insert the customer */
    public static function insertCustomer($input)
    {
        $customers = new Customers;
        $customers->authentication_id = $input['customer_authentication_id'];
        $customers->email = $input['customer_email'];
        $customers->country_code = '+95';
        $customers->phone = $input['customer_mobile'];
        $customers->firstname = $input['customer_first_name'];
        $customers->lastname = $input['customer_last_name'];
        $customers->alternate_mobile = null;
        $customers->alternate_mobile_country_code = '';
        $customers->gender = isset($input['customer_gender']) ? $input['customer_gender'] : '';
        $customers->dob = isset($input['customer_dob']) ? date('Y-m-d', strtotime($input['customer_dob'])) : '';
        $customers->status = '1';
        $customers->verified = '1';
        $customers->save();
        return $customers->id;
    }

}
