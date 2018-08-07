<?php
require_once('../secrets.php');
require_once('helpers/validation.php');

$snipe_id;

if ( isset($_GET['snipe_id']) ){
	$snipe_id = $_GET['snipe_id'] ;

	// do data sanitization
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

// Snipe api call
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

// echo snipe errors if any
if ( $json["status"] == "error" ) {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
	exit(1);
} 
else {
	echo json_encode(array( 'status'=>'success', 'message'=>'Asset checked in') );
}

?>