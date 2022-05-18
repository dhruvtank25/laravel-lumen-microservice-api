<?php

namespace App\Http\V_1_0_0\InsertUpdate\Models\Orders;

use Illuminate\Database\Eloquent\Model;

class Customer_address_masters extends Model
{
    /* To check the login credential is valid or not */
    public static function getAddres($address_id)
    {
        return Customer_address_masters::where('akeed_address_id', '=', $address_id)->first();
    }
    /* To insert the customer address */
    public static function insertCustomerAddress($input)
    {
        $address = new Customer_address_masters;
        $address->customer_id = $input['customer_id'];
        $address->akeed_address_id = $input['customer_address_id'];
        $address->address_type = $input['customer_address_type'];
        $address->house_no = $input['customer_house_no'];
        $address->landmark = $input['customer_landmark'];
        $address->address = $input['customer_address'];
        $address->latitude = isset($input['customer_address_latitude']) ? $input['customer_address_latitude'] : '0.0';
        $address->longitude = isset($input['customer_address_longitude']) ? $input['customer_address_longitude'] : '0.0';
        $address->delivery_note = isset($input['customer_address_delivery_note']) ? $input['customer_address_delivery_note'] : null;
        $address->save();
        return $address->id;
    }

}
