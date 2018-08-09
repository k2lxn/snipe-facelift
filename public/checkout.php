<?php
require_once('../secrets.php');
require_once('helpers/validation.php');

// need snipe_id, assignee_id, (optional) expected checkout, and (optional) checkout date (default to today)
$snipe_id;
$assignee_id;
$checkout_date;
$new_checkin_date;

// snipe_id validation
if ( isset($_GET['snipe_id']) ){
	$snipe_id = sanitize_snipe_id( $_GET['snipe_id'] );

	if ( $snipe_id == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid snipe_id' ) );
		exit(1);
	}
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'No snipe_id given' ) );
	exit(1);
}


// assignee_id validation
// if extending, assignee_id should have been sent with request
if ( isset($_GET['assignee_id']) ){
	$assignee_id = sanitize_snipe_id( $_GET['assignee_id'] );

	if ( $assignee_id == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid Snipe user id' ) );
		exit(1);
	}
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'No assignee_id given' ) );
	exit(1);
}

// if this is a new checkout, user netID needs to be validated and converted to snipe id
// ...


// date string validation
if ( isset($_GET['checkout_date']) ){
	$checkout_date = sanitize_date( $_GET['checkout_date'] );
	
	if ( $checkout_date == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid checkout date given. Use format YYYY-MM-DD.' ) );
		exit(1);
	}
}
// default to today's date
else {
	$checkout_date = date("Y-m-d");
}

if ( isset($_GET['new_checkin_date']) ){
	$new_checkin_date = sanitize_date( $_GET['new_checkin_date'] );
	
	if ( $new_checkin_date == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid expected checkin date given. Use format YYYY-MM-DD.' ) );
		exit(1);
	}

	if ( !date_is_in_future( $new_checkin_date ) ) {
		echo json_encode( array('status'=>'error', 'message'=>'Expected checkin date must be in the future.' ) );
		exit(1);
	}
}


// Snipe api call
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);


$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin_date;

$ch = curl_init( $url );

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

$success_message = "Loan extended to {$new_checkin_date}" ;

if ( $json["status"] == "success") {
	echo json_encode( array('status'=>'success',
							'message'=>$success_message) );
}
else {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
}


?>