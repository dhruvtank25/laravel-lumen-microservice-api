<?php
use GuzzleHttp\Client;
use Guzzle\Http\Exception\ServerErrorResponseException;
namespace App\Helpers;

class SendSms
{
	//if (!function_exists('send_sms')) {

		public static function send_sms($message,$phone)
		{
			$smsusername = env('SMS_USERNAME');
			$smspassword = env('SMS_PASSWORD');
			try{
				$client = new \GuzzleHttp\Client();
				$request = $client->get('https://sms.ooredoo.com.om/user/smspush.aspx?phoneno='.$phone.'&message='.$message.'&sender=Akeed&username='.$smsusername.'&password='.$smspassword.'&source=API');
				$response = $request->getBody()->getContents();
				return true;
				//echo '<pre>';
				//print_r($response);
				//exit;
			}
			catch( Exception $e){
				print_r($e);
			}
		}
	  
	//}
}