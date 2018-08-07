<?php
require_once('../secrets.php');
require_once('helpers/validation.php');

// need snipe_id, assignee_id, (optional) expected checkout, and (optional) checkout date (default to today)
$snipe_id;
$assignee_id;
$checkout_date;
$expected_checkin;

// snipe_id validation
if ( isset($_GET['snipe_id']) ){
	$snipe_id = $_GET['snipe_id'] ;
	$snipe_id = validate_snipe_id( $_GET['snipe_id'] );

	if ( $snipe_id == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid snipe_id' ) );
		exit(1);
	}
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'No snipe_id given' ) );
	exit(1);
}


// assignee validation
// if extending, assignee_id should have been sent with request


// if this is a new checkout, user netID needs to be validated and converted to snipe id






$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);


$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin;

$ch = curl_init( $url );

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

$success_message = "Loan extended to {$new_checkin}" ;

if ( $json["status"] == "success") {
	echo json_encode( array('status'=>'success',
							'message'=>$success_message) );
}
else {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
}


?>