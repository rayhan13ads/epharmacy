<?php
/* PHP */

ob_start();
session_start();
require_once('../../admin/inc/config.php');

$error_message = '';

$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $paypal_email = $row['paypal_email'];
}

$return_url = 'http://localhost:9000/payment/sslcommerz/ssl_verify_process.php';
$cancel_url = 'payment/sslcommerz/cancel.php';
$fail_url = 'payment/sslcommerz/fail.php';
$notify_url = 'payment/sslcommerz/ssl_verify_process.php';

$item_name = 'Product Item(s)';
$item_amount = $_POST['final_total'];
$item_number = time();

$payment_date = date('Y-m-d H:i:s');


$payment_date = date('Y-m-d H:i:s');
$payment_id = time();

$statement = $pdo->prepare("INSERT INTO tbl_payment (   
	                            customer_id,
	                            customer_name,
	                            customer_email,
	                            payment_date,
	                            txnid, 
	                            paid_amount,
	                            card_number,
	                            card_cvv,
	                            card_month,
	                            card_year,
	                            bank_transaction_info,
	                            payment_method,
	                            payment_status,
	                            shipping_status,
	                            payment_id
	                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$statement->execute(array(
    $_SESSION['customer']['cust_id'],
    $_SESSION['customer']['cust_name'],
    $_SESSION['customer']['cust_email'],
    $payment_date,
    '',
    $item_amount,
    '',
    '',
    '',
    '',
    '',
    'SSLCommerz',
    'Pending',
    'Pending',
    $payment_id
));

$i = 0;
foreach ($_SESSION['cart_p_id'] as $key => $value) {
    $i++;
    $arr_cart_p_id[$i] = $value;
}

$i = 0;
foreach ($_SESSION['cart_p_name'] as $key => $value) {
    $i++;
    $arr_cart_p_name[$i] = $value;
}

$i = 0;
foreach ($_SESSION['cart_size_name'] as $key => $value) {
    $i++;
    $arr_cart_size_name[$i] = $value;
}

$i = 0;
foreach ($_SESSION['cart_color_name'] as $key => $value) {
    $i++;
    $arr_cart_color_name[$i] = $value;
}

$i = 0;
foreach ($_SESSION['cart_p_qty'] as $key => $value) {
    $i++;
    $arr_cart_p_qty[$i] = $value;
}

$i = 0;
foreach ($_SESSION['cart_p_current_price'] as $key => $value) {
    $i++;
    $arr_cart_p_current_price[$i] = $value;
}

$i = 0;
$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $i++;
    $arr_p_id[$i] = $row['p_id'];
    $arr_p_qty[$i] = $row['p_qty'];
}

for ($i = 1; $i <= count($arr_cart_p_name); $i++) {
    $statement = $pdo->prepare("INSERT INTO tbl_order (
	                        product_id,
	                        product_name,
	                        size, 
	                        color,
	                        quantity, 
	                        unit_price, 
	                        payment_id
	                        ) 
	                        VALUES (?,?,?,?,?,?,?)");
    $sql = $statement->execute(array(
        $arr_cart_p_id[$i],
        $arr_cart_p_name[$i],
        $arr_cart_size_name[$i],
        $arr_cart_color_name[$i],
        $arr_cart_p_qty[$i],
        $arr_cart_p_current_price[$i],
        $payment_id
    ));

    // Update the stock
    for ($j = 1; $j <= count($arr_p_id); $j++) {
        if ($arr_p_id[$j] == $arr_cart_p_id[$i]) {
            $current_qty = $arr_p_qty[$j];
            break;
        }
    }
    $final_quantity = $current_qty - $arr_cart_p_qty[$i];
    $statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
    $statement->execute(array($final_quantity, $arr_cart_p_id[$i]));
}
unset($_SESSION['cart_p_id']);
unset($_SESSION['cart_size_id']);
unset($_SESSION['cart_size_name']);
unset($_SESSION['cart_color_id']);
unset($_SESSION['cart_color_name']);
unset($_SESSION['cart_p_qty']);
unset($_SESSION['cart_p_current_price']);
unset($_SESSION['cart_p_name']);
unset($_SESSION['cart_p_featured_photo']);

if ($sql) {

    $post_data = array();
    $post_data['store_id'] = "arabi655a442b84a02";
    $post_data['store_passwd'] = "arabi655a442b84a02@ssl";
    $post_data['total_amount'] = $item_amount;
    $post_data['currency'] = "BDT";
    $post_data['tran_id'] = "SSLCZ_TEST_" . uniqid();
    $post_data['success_url'] = $return_url;
    $post_data['fail_url'] = $fail_url;
    $post_data['cancel_url'] = $cancel_url;
    # $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

    # EMI INFO
    #$post_data['emi_option'] = "1";
    #$post_data['emi_max_inst_option'] = "9";
    #$post_data['emi_selected_inst'] = "9";

    # CUSTOMER INFORMATION
    $post_data['cus_name'] = $_SESSION['customer']['cust_name'];
    $post_data['cus_email'] = $_SESSION['customer']['cust_email'];
    $post_data['cus_add1'] = $_SESSION['customer']['cust_address'];
    $post_data['cus_add2'] = $_SESSION['customer']['cust_b_address'];
    $post_data['cus_city'] = $_SESSION['customer']['cust_city'];
    $post_data['cus_state'] = $_SESSION['customer']['cust_state'];
    $post_data['cus_postcode'] = $_SESSION['customer']['cust_zip'];
    $post_data['cus_country'] = "Bangladesh";
    $post_data['cus_phone'] = $_SESSION['customer']['cust_phone'];;
    #$post_data['cus_fax'] = "01711111111";

    # SHIPMENT INFORMATION
    $post_data['ship_name'] = "";
    $post_data['ship_add1 '] = "";
    $post_data['ship_add2'] = "";
    $post_data['ship_city'] = "";
    $post_data['ship_state'] = "";
    $post_data['ship_postcode'] = "";
    $post_data['ship_country'] = "Bangladesh";

    # OPTIONAL PARAMETERS
    $post_data['value_a'] = $payment_id;
    $post_data['value_b '] = "";
    $post_data['value_c'] = "";
    $post_data['value_d'] = "";

    $cart_data = array();

    for ($i = 1; $i <= count($arr_cart_p_name); $i++) {
        $cart_data["product"] = $arr_cart_p_name[$i];
        $cart_data["amount"] = $arr_cart_p_current_price[$i];
    }

    # CART PARAMETERS
    $post_data['cart'] = json_encode($cart_data);
    $post_data['product_amount'] = $item_amount;
    $post_data['vat'] = "0";
    $post_data['discount_amount'] = "0";
    $post_data['convenience_fee'] = "0";



    # REQUEST SEND TO SSLCOMMERZ
    $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $direct_api_url);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($handle, CURLOPT_POST, 1);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC


    $content = curl_exec($handle);

    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

    if ($code == 200 && !(curl_errno($handle))) {
        curl_close($handle);
        $sslcommerzResponse = $content;
    } else {
        curl_close($handle);
        echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
        exit;
    }

    # PARSE THE JSON RESPONSE
    $sslcz = json_decode($sslcommerzResponse, true);

    if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
        # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
        # echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
        echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
        # header("Location: ". $sslcz['GatewayPageURL']);
        exit;
    } else {
        echo "JSON Data parsing error!";
    }
}
