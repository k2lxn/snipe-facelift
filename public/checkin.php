<?php
require_once('../secrets.php');
require_once('helpers/validation.php');
require_once('helpers/snipe_calls.php');

$snipe_id;

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

// Snipe api call
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin' ;

$response = snipe_call( $url, 'POST', $headers );

$json = json_decode($response, true);

// echo snipe errors if any
if ( $json["status"] == "error" ) {
	echo json_encode( array( 'status'=>'error', 'message'=>$json["messages"] ) );
} 
elseif ( $json["status"] == "success" ) {
	echo json_encode( array( 'status'=>'success', 'message'=>'Asset checked in' ) );
}
else {
	// Should never get here
	echo json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}

?>