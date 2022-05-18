<?php

namespace App\Helpers;

class CommonFunction
{
    public static function getPaymentMode($payment_id, $language = '')
    {
        switch ($payment_id) {
            case 1:
                if ($language == 'ar')
                    return 'الدفع عن الاستلام';
                else
                    return 'Cash On Delivery';
            case 2 :
                if ($language == 'ar')
                    return 'بطاقة عند التسليم';
                else
                    return 'Card On Delivery';
            case 3:
                if ($language == 'ar')
                    return 'بطاقة ائتمان';
                else
                    return 'Credit Card';
            case 4:
                if ($language == 'ar')
                    return 'بطاقة ائتمان';
                else
                    return 'Debit Card';
            case 5:
                if ($language == 'ar')
                    return 'لا الدفع';
                else
                    return 'No Payment';
            default:
                if ($language == 'ar')
                    return 'الدفع عن الاستلام';
                else
                    return 'Cash On Delivery';
        }
    }
}