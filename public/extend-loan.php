<?php
require_once( 'config.php' );
require_once( $path_to_secrets . 'secrets.php' );
require_once( $path_to_includes .'validation.php' );
require_once( $path_to_includes . 'snipe_calls.php' );

session_start();

// need snipe_id, assignee_id, (optional) expected checkout, and (optional) checkout date (default to today)
$snipe_id;
$assignee_id;
$checkout_date;
$new_checkin_date;
$asset_name;

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

//validate asset_name
if ( isset($_GET['asset_name']) ){
	$asset_name = sanitize_asset_name( $_GET['asset_name'] );

	if ( $asset_name == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid asset_name' ) );
		exit(1);
	}
}
else {
	$asset_name = "" ;
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


// Snipe api calls
// checkin
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin?name=' . $asset_name ;

$response = snipe_call( $url, 'POST', $headers );
$json = json_decode($response, true);

$success_message = "Loan extended to {$new_checkin_date}" ;

if ( $json["status"] == "error" ) {
	echo json_encode( array( 'status'=>'error', 'message'=>$json["messages"] ) );
	exit(1);
} 

// re-checkout
elseif ( $json["status"] == "success" ) {

	$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin_date ;

	$response = snipe_call( $url, 'POST', $headers );
	$json = json_decode($response, true);

	if ( $json["status"] == "error" ) {
		echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
	}
	elseif (  $json["status"] == "success"  ) {
		echo json_encode( array('status'=>'success',
						   'message'=>$success_message) );
	}
	else {
		// Should never get here
		echo json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
	}
}
else {
	// Should never get here
	echo json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}

?>








