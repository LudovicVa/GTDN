<?php

//Status
$G_status = 'IPN Not Init';

function generateRef() {
	$rand1 = rand(1000,9999);
	$rand2 = rand(1000,9999);
	return $rand1 . "-" . $rand2;
}

ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');

//Generate ref
$G_Voucher = generateRef();

// instantiate the IpnListener class
include('./util/ipnlistener.php');
$G_listener = new IpnListener();

function mailDeveloper($error_name, $error_info) {
	global $G_status, $G_Voucher, $G_listener;
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: \"no-reply-getthedealnow\"\n";
	
	$body =
	'<html>
      <head>
       <title>Payment received for '. $name . '</title>
      </head>
      <body>
		<p>An error occurred during a transaction !</p>
		<p> ' . $error_name . ' :<br>
		' . $error_info . '</p>
		<p> Status when the error happened : ' . $G_status . '</p>
		<p> Code: <b>'. $G_Voucher .'</p>		
		<p>IPN:<br/><pre>';
		if($G_listener != null) {
			$body .= $G_listener->getTextReport();		
		}
	$body .= '</pre></p>
      </body>
     </html>';
	 
	//Global variable
	$developper_mail= "ludovic-vanhove@orange.fr";
	$developper_prefix = "[GetTheDealNow Debug]";
	 
	mail($developper_mail, $developper_prefix . " " .$error_name, $body, $headers);
	
	error_log($error_name . ' => ' . $error_info . '\n');
}


//---------------------------------
//$G_listener->use_sandbox = true;
//---------------------------------

try {
    $G_listener->requirePostMethod();
    $verified = $G_listener->processIpn();
} catch (Exception $e) {
	mailDeveloper('Error when pre-processing the IPN', $e->getMessage());
    exit(0);
}

$G_status = 'IPN Initialized';

function getDB() {
	$dsn = 'mysql:host=mysql6.000webhost.com;dbname=a9530059_data';
	$username = 'a9530059_admin';
	$password = 'strubbel89';
	try {
		$dbh = new PDO($dsn, $username, $password);
	} catch (PDOException $e) {
		mailDeveloper('Could not connect to DB' , $e->getMessage());		
    }
	return $dbh;
}

function insertTransaction($db, $payer_name, $payer_email, $item_number, $paypal, $receipt) {
	//Insert transaction into transaction DB
	$statement = $db->prepare("INSERT INTO transactions(customer_name, customer_email, id_product, paypal_id, receipt_id) VALUES (:cname, :cmail, :id_product, :paypal_id, :receipt_id)");
	$statement->bindParam(':cname', $payer_name);
	$statement->bindParam(':cmail', $payer_email);
	//NEED TO BE CHECKED BEFORE !!
	$statement->bindParam(':id_product', $item_number);
	$statement->bindParam(':paypal_id', $paypal);
	$statement->bindParam(':receipt_id', $receipt);
	
	//Execute transaction DB insert
	try {		
		$statement->execute();
	} catch (PDOException $e) {
		mailDeveloper('Problem when inserting the transaction ' , $e->getMessage());		
		exit(0);
    }
}

function getTraderInfo($db, $product_id) {
	$statement = $db->prepare("SELECT trader.name, trader.mail, trader.free_text FROM `trader`,`products` WHERE (products.id_product = ? AND products.id_trader = trader.id_trader)");	
	
	try {
		$statement->execute(array($product_id));
	} catch (PDOException $e) {
		mailDeveloper('Problem when retrieving trader information ' , $e->getMessage());		
		exit(0);
    }
	
	$result = $statement->fetchAll();
	if(count($result) != 1) {
		mailDeveloper('No trader existing or multiple traders' , 'id : ' . $product_id . '; trader count : ' . count($result));
		exit(0);
	}	
	
	$table['mail_trader'] 	= $result[0]['mail'];
	$table['name'] 			= $result[0]['name'];
	$table['free_text'] 	= $result[0]['free_text'];
	return $table;
}

function getProductInfo($db, $product_id) {
	if(is_numeric($product_id)) {
		$statement = $db->prepare("SELECT * FROM products WHERE id_product = ? ");
		try {
			$statement->execute(array($product_id));
		} catch (PDOException $e) {
			mailDeveloper('Problem when retrieving product information ' , $e->getMessage());		
			exit(0);
		}
		$product_info = $statement->fetchAll();
		if(count($product_info) != 1) {
			mailDeveloper('More than one product found with specific id or 0' , 'id : ' . $product_id . '; product count : ' . count($product_info));
			exit(0);
		}		
	} else {
		mailDeveloper('Given item number is not a number' , $product_id);
		exit(0);
	}
	return $product_info[0];
}


if ($verified) {
	$G_status = "IPN Verified";
	
	if($_POST['payment_status'] != "Completed") {
		//ignored as it not completed
		exit(0);
	}
	
	//To Be Changed !!!
	if($_POST['receiver_email'] != "contact@BIGGER-stronger.com") {
		//ignored as we are not the receiver
		mailDeveloper('Received a payment from someone else' , $_POST['receiver_email']);
		exit(0);
	}
	
	$G_status = "IPN Receiver Email Verified";
	
	//Init db connection
	$db = getDB();
	
	//Check that the id product exists and get product info
	$product_id = $_POST['item_number'];
	$product_info = getProductInfo($db, $product_id);
	
	$G_status = "Product Found";	
	
	if(floatval($_POST['mc_gross']) != floatval($product_info['price'])) {
		mailDeveloper('Received a payment of ' .  $product_info['name'] . ' with wrong price !', 'Received ' . $_POST['mc_gross']. ', expected ' . $product_info['price']);
		exit(0);
	}
	
	//Insert transaction into transaction DB
	$payer_name = $_POST['first_name'] . " " . $_POST['last_name'];
	insertTransaction($db, $payer_name,  $_POST['payer_email'],  $_POST['item_number'], $_POST['txn_id'], $G_Voucher);
	
	$G_status = "Transaction inserted";	
	
	//Get the trader email associated with the product
	$trader = getTraderInfo($db, $_POST['item_number']);
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: \"no-reply-getthedealnow\"\n";
	
	$subject = ' New order from GetTheDealNow.com';
	$nico_email = "nicolas0vanhove@gmail.com";
	
	//Mail to trader
	$body =
	'<html>
      <head>
       <title>Payment received for '. $name . '</title>
      </head>
      <body>
		Dear Merchant,
		<p>Congratulations, you have received a new order from GetTheDealNow.com. Please make sure that you request the following from the Client when he redeems the deal:</p>
		<p>Voucher Code: <b>'. $G_Voucher .'</b><br>
		Customer Name: <b>' . $payer_name . '</b><br>
		Item: <b>' . $product_info['name'] . '</b></p>
		<p>Please note that this Voucher Code is only valid once.</p>
		<p>Thank you!<br>
		The GetTheDealNow Team<br>
		5265 0532</p>
      </body>
     </html>';
	 
    mail($nico_email . ',' . $trader['mail_trader'], $subject, $body, $headers);
			
	$G_status = "Trader mail sent";		
	
	//Send email to customer
	$payer_mail = $_POST['payer_email'];
	$subject = "GetTheDealNow - Your deal is ready!";
	$body =
	'<html>
      <head>
       <title>GetTheDealNow - Your deal is ready!</title>
      </head>
      <body>
		Your deal is ready !
		<p>Get excited, '. $_POST['first_name'] . ', your voucher is ready to use. Your voucher code is: <b>'. $G_Voucher .'</b>.
		<p>You can redeem the item "'. $product_info['name'] . '" from now on until the promotion expires at one of the addresses below by simply state your name and the voucher code:
		</p>
		<p>'. $trader['free_text']
		.'</p>
		<p>Go get the deal now, and see you soon on our website for more last minute deals!</p>
		<p>The GetTheDealNow.com Team
		---
		<br>contact@getthedealnow.com
		<br>Like us on Facebook today and ask for 5% off on your next deal!</p>
	   </body>
	 </html>
	';
	mail($nico_email . ',' . $payer_mail, $subject, $body, $headers);

	$G_status = "Customer mail sent";	
	
	//Send email to nico
	$subject = 'GetTheDealNow - Purchase made for ' . $product_info['name'];
	$body =	'<html>
      <head>
     </head>
      <body>
		Dear GetTheDealNow team,
		<p>Get excited, the script is working and people are purchasing stuff !! '. $payer_name . ' just bought ' . $product_info['name'] . '</p>
		<p>The voucher is : ' . $G_Voucher .'</p>
	';
	mail($nico_email . ', ludovic-vanhove@orange.fr', $subject, $body, $headers);
	
	$G_status = "Success";	
	///jack@mrchatte.com.hk,tiffany@mrchatte.com.hk,tracy@mrchatte.com.hk,lim@mrchatte.com.hk,janet@mrchatte.com.hk,bonnie@mrchatte.com.hk,channie@mrchatte.com.hk,caroline@mrchatte.com.hk,charline@mrchatte.com.hk

} else {
    /*!
    An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
    a good idea to have a developer or sys admin manually investigate any 
    invalid IPN.
    */
	mailDeveloper('IPN Not Verified', '');
}

?>

<?php
/*
function get_price_item(int $id) {

}

function getDB() {
	$dsn = 'mysql:host=mysql6.000webhost.com;dbname=a9530059_data';
	$username = 'a9530059_admin';
	$password = 'strubbel89';
	try {
		$dbh = new PDO($dsn, $username, $password);
	} catch (PDOException $e) {
        error_log("Could not connect to db ! \n ");       
    }
	return $dbh;
}

function generateRef() {
	$rand1 = rand(1000,9999);
	$rand2 = rand(1000,9999);
	return $rand1 . "-" . $rand2;
}

function insertTransaction($db, $payer_name, $payer_email, $item_number, $paypal, $receipt) {
	//Insert transaction into transaction DB
	$statement = $db->prepare("INSERT INTO transactions(customer_name, customer_email, id_product, paypal_id, receipt_id) VALUES (:cname, :cmail, :id_product, :paypal_id, :receipt_id)");
	$statement->bindParam(':cname', $name);
	$statement->bindParam(':cmail', $payer_email);
	//NEED TO BE CHECKED BEFORE !!
	$statement->bindParam(':id_product', $item_number);
	$statement->bindParam(':paypal_id', $paypal);
	$statement->bindParam(':receipt_id', $receipt);
	
	$name = $_POST['first_name'] . " " . $_POST['last_name'];
	
	//Execute transaction DB insert
	try {		
		$statement->execute();
	} catch (PDOException $e) {
        error_log($e->getMessage() . " \n "); 
		mail('ludovic-vanhove@orange.fr', 'Problem in transaction db ', $e->getMessage() . " \n" );
		exit(0);
    }
}

function getTraderInfo($db, $product_id) {
	$statement = $db->prepare("SELECT trader.name, trader.mail, trader.free_text FROM `trader`,`products` WHERE (products.id_product = ? AND products.id_trader = trader.id_trader)");	
	$statement->execute(array($product_id));
	$result = $statement->fetchAll();
	if(count($result) != 1) {
		mail('ludovic-vanhove@orange.fr', 'Error, multiple traders ?? ',  serialize($result));
		exit(0);
	}	
	error_log(serialize($result));
	$table['mail_trader'] 	= $result[0]['mail'];
	$table['name'] 			= $result[0]['name'];
	$table['free_text'] 	= $result[0]['free_text'];
	return $table;
}

ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


// instantiate the IpnListener class
include('./util/ipnlistener.php');
$listener = new IpnListener();

$listener->use_sandbox = true;

try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(0);
}

if ($verified) {
    /*
    Once you have a verified IPN you need to do a few more checks on the POST
    fields--typically against data you stored in your database during when the
    end user made a purchase (such as in the "success" page on a web payments
    standard button). The fields PayPal recommends checking are:
    
        1. Check the $_POST['payment_status'] is "Completed"
	    2. Check that $_POST['txn_id'] has not been previously processed 
	    3. Check that $_POST['receiver_email'] is your Primary PayPal email 
	    4. Check that $_POST['payment_amount'] and $_POST['payment_currency'] 
	       are correct
    
    Since implementations on this varies, I will leave these checks out of this
    example and just send an email using the getTextReport() method to get all
    of the details about the IPN.  
    *
	if($_POST['payment_status'] != "Completed") {
		//ignored as it not completed
		exit(0);
	}
	
	//To Be Changed !!!
	if($_POST['receiver_email'] != "seller@paypalsandbox.com") {
		//ignored as we are not the receiver
		error_log("Weird, receiver was : " . $_POST['receiver_email'] . "\n");
		exit(0);
	}
	
	//Init db connection
	$db = getDB();
	
	//Check that the id product exists
	$product_id = $_POST['item_number'];
	if(is_numeric($product_id)) {
		$statement = $db->prepare("SELECT * FROM products WHERE id_product = ? ");
		$statement->execute(array($product_id));
		$product_info = $statement->fetchAll();
		if(count($product_info) != 1) {
			mail('ludovic-vanhove@orange.fr', 'error multiple', 'Error in product id '. $product_id. ' ! ' . count($product_info) . $listener->getTextReport());
			exit(0);
		}		
	} else {
		mail('ludovic-vanhove@orange.fr', 'error', 'Error in product id !' . $listener->getTextReport());
		exit(0);
	}
	
	//Generate ref
	$ref = generateRef();
	
	//Insert transaction into transaction DB
	$payer_name = $_POST['first_name'] . " " . $_POST['last_name'];
	insertTransaction($db, $payer_name,  $_POST['payer_email'],  $_POST['item_number'], $_POST['txn_id'], $ref);
	
	//Get the trader email associated with the product
	$trader = getTraderInfo($db, $_POST['item_number']);
	error_log(serialize($trader));
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: \"no-reply-getthedealnow\"\n";
	$headers .= "Reply-to: \"contact@bigger-stronger.com\"\n";
	
	$subject = ' New order from GetTheDealNow.com';
	
	//Mail to trader
	$body =
	'<html>
      <head>
       <title>Payment received for '. $name . '</title>
      </head>
      <body>
		<p>Dear Merchant,<p>
		<p>Congratulations, you have received a new order from GetTheDealNow.com. Please make sure that you request the following from the Client when he redeems the deal:</p>
		<p>Voucher Code: <b>'. $ref .'</b><br>
		Customer Name: <b>' . $payer_name . '</b><br>
		Item: <b>' . $product_info[0]['name'] . '</b></p>
		<p>Please note that this Voucher Code is only valid once.</p>
		<p>Thank you!<br>
		The GetTheDealNow Team</p>
		<p>5265 0532</p>
      </body>
     </html>';
	 
    mail($trader['mail_trader'], $subject, $body, $headers);
		
	//Send email to customer
	$payer_mail = $_POST['payer_email'];
	$subject = "GetTheDealNow - Your deal is ready!";
	$body =
	'<html>
      <head>
       <title>GetTheDealNow - Your deal is ready!</title>
      </head>
      <body>
		<p>Your deal is ready !</p>
		<p>Get excited, '. $_POST['first_name'] . ', your voucher is ready to use. Your voucher code is: <b>'. $ref .'</b>.
		<p>You can redeem the item "'. $product_info[0]['name'] . '" from now on until the promotion expires at one of the addresses below by simply state your name and the voucher code:
		</p>
		<p>'. $trader['free_text']
		.'</p>
		<p>Go get the deal now, and see you soon on our website for more last minute deals!</p>
		<p>The GetTheDealNow.com Team</p>
		---
		<br>Like us on Facebook today and ask for 5% off on your next deal!
	';
	mail($payer_mail, $subject, $body, $headers);

	//Send email to nico
	$nico_email = "ludovic-vanhove@orange.fr,nicolas0vanhove@gmail.com";
	$subject = 'GetTheDealNow - Purchase made for ' . $product_info[0]['name'];
	$body =	'<html>
      <head>
       <title>GetTheDealNow - Your deal is ready!</title>
      </head>
      <body>
		<p>Dear Nicolas,</p>
		<p>Get excited, the script is working ! '. $payer_name . ' just bought ' . $product_info[0]['name'] . '</p>
		<p>The voucher is : ' . $ref .'</p>
	';
	mail($nico_email, $subject, $body, $headers);
	///jack@mrchatte.com.hk,tiffany@mrchatte.com.hk,tracy@mrchatte.com.hk,lim@mrchatte.com.hk,janet@mrchatte.com.hk,bonnie@mrchatte.com.hk,channie@mrchatte.com.hk,caroline@mrchatte.com.hk,charline@mrchatte.com.hk
	require_once('./util/class.phpmailer.php');
	/*$mail                = new PHPMailer();

	$mail->IsSMTP(); // telling the class to use SMTP
	//$mail->Host       = "mail.gmail.com"; // SMTP server
	$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
	$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
	$mail->Username   = "ludovic0vanhove@gmail.com";  // GMAIL username
	$mail->Password   = "piyuDak9";  
	$mail->SetFrom('ludovic0vanhove@gmail.com', 'List manager');
	
	$mail->AddAddress($mail_trader, $name_trader);

	$mail->Subject       = "PHPMailer Test Subject via smtp, basic with authentication";
	$mail->MsgHTML("Hello " . $name_trader . ", you received a command !");
	if(!$mail->Send()) {
		  error_log($mail->ErrorInfo);
	}*
	
	//mail('nicolas0vanhove@gmail.com', 'Verified IPN', $listener->getTextReport());
	//echo $listener->getTextReport();

} else {
    /*
    An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
    a good idea to have a developer or sys admin manually investigate any 
    invalid IPN.
    *
    mail('ludovic-vanhove@orange.fr', 'Invalid IPN', $listener->getTextReport());
}*/

?>

