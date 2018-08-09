<?php
require_once('../secrets.php');
require_once('helpers/validation.php');
require_once('helpers/snipe_calls.php');

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

// checkin
$response = checkin( $snipe_id, $headers ) ;
$json = json_decode( $response, true ) ;

if ( $json["status"] == "error" ) {
	echo $response ;
	exit(1);
}

// re-checkout
$response = checkout( $snipe_id, $assignee_id, $checkout_date, $new_checkin_date, $headers );
echo $response ;

?>








