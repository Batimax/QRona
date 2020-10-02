<?php

require_once __DIR__.'/../../admin/init.php';

use Twilio\Rest\Client;

function sendSMS($dtb, $userID, $phone_number) {

	// generate rdm number
	$verif_code = rand(100000, 999999);

		// $status_api = APIsms($verif_code, $phone_number);
		$status_api = 'sucscess';
		// If sms correctly sent
		if ($status_api == 'success'){
			storeRdmNbrDB($dtb, $verif_code, $userID);
		}

		$status = $status_api;
		return $status;
};

function APIsms ($verif_code,$phone_number){

	// Your Account SID and Auth Token from twilio.com/console
	$TWILIO_SID = decryptEncryptedEnv('TWILIO_SID');
	$TWILIO_AUTH_TOKEN = decryptEncryptedEnv('TWILIO_AUTH_TOKEN');

	$sms_content = $verif_code . ' : Satellite phone number verification code <3';

	// A Twilio number you own with SMS capabilities
	$twilio_number = "+12408835962";
	$client = new Client($TWILIO_SID, $TWILIO_AUTH_TOKEN);

	try {
		$status = 'success';
			$client->messages->create(
		// Where to send a text message (your cell phone?)
		$phone_number,
		array(
			'from' => $twilio_number,
			'body' => $sms_content,
		)
	);
    } catch ( Exception $e ) {
         if ($e->getCode() == 21211){
			$status = 'twilio_phone_not_valid';
		 } else if ($e->getCode() == 21408) {
			$status = 'twilio_unauthorized_country';
		 }
		 else {
			 echo $e->getCode();
			  $status = 'error';
		 }
	}
	return $status;
}

function storeRdmNbrDB($dtb, $verif_code, $userID){

	$req = $dtb->prepare("UPDATE users
		SET verification_nbr = :verif_code
		WHERE users.userID = :userID");
	$req->execute(array(
		'verif_code' => $verif_code,
		'userID' => $userID
	));
	$req->closeCursor();

};

function checkCode($dtb, $userID, $code) {

	$req = $dtb->prepare('SELECT verification_nbr FROM users WHERE users.userID = :userID');
	$req->execute(array(
		'userID' => $userID
	));
	$gen_code = $req->fetch();
	$req->closeCursor();

	if ($gen_code) {
		$gen_code = $gen_code['verification_nbr'];
		if ($gen_code == $code) {
			return 'success_check_code';
		} else {
			return 'wrong_code';
		}
	} else {
		return 'no_code_for_user';
	}
}

function userValidatePhoneNumber($dtb, $userID, $phone_number) {
	// Change number verified flag to true
	$req = $dtb->prepare("UPDATE users
		SET nbr_verified = 1
		WHERE users.userID = :userID");
	$req->execute(array(
		'userID' => $userID
	));
	$req->closeCursor();

	// Update phone number
	$req = $dtb->prepare("UPDATE users
		SET phone = :phone
		WHERE users.userID = :userID");
	$req->execute(array(
		'userID' => $userID,
		'phone' => $phone_number
	));
	$req->closeCursor();

	// Delete code
	$req = $dtb->prepare("UPDATE users
		SET verification_nbr = NULL
		WHERE users.userID = :userID");
	$req->execute(array(
		'userID' => $userID
	));
	$req->closeCursor();


}
