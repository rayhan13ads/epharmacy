<?php
require_once "../../admin/inc/config.php";

$val_id=urlencode($_POST['val_id']);
$store_id=urlencode("arabi655a442b84a02");
$store_passwd=urlencode("arabi655a442b84a02@ssl");
$requested_url = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=1&format=json");

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requested_url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

$result = curl_exec($handle);

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if($code == 200 && !( curl_errno($handle)))
{

	# TO CONVERT AS ARRAY
	# $result = json_decode($result, true);
	# $status = $result['status'];

	# TO CONVERT AS OBJECT
	$result = json_decode($result);
	

	# TRANSACTION INFO
	$status = $result->status;
	$tran_date = $result->tran_date;
	$tran_id = $result->tran_id;
	$val_id = $result->val_id;
	$amount = $result->amount;
	$store_amount = $result->store_amount;
	$bank_tran_id = $result->bank_tran_id;
	$card_type = $result->card_type;

	# EMI INFO
	#$emi_ instalment = $result->emi_instalment;
	// $emi_ amount = $result->emi_ amount;
	// $emi_description = $result->emi_description;
	// $emi_issuer = $result->emi_issuer;

	# ISSUER INFO
	$card_no = $result->card_no;
	$card_issuer = $result->card_issuer;
	$card_brand = $result->card_brand;
	$card_issuer_country = $result->card_issuer_country;
	$card_issuer_country_code = $result->card_issuer_country_code;

	# API AUTHENTICATION
	$APIConnect = $result->APIConnect;
	$validated_on = $result->validated_on;
	$gw_version = $result->gw_version;

	$payment_id = $result->value_a;

	if ($payment_id) {
		if($status == "VALID"){
			$statement = $pdo->prepare("UPDATE tbl_payment SET 
                        txnid=?, 
                        payment_status=?
                        WHERE payment_id=?");
			$sql = $statement->execute(array(
				$tran_id,
				$status,
				$payment_id
			));
			header('location: ../../payment_success.php');

		}else{
			$statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
			$sql = $statement->execute(array($payment_id));
			header('location: ../../checkout.php');

		}
		


		
	}

	


    

} else {

	echo "Failed to connect with SSLCOMMERZ";
}



?>