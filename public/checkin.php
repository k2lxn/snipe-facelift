<?php
require_once( 'config.php' );
require_once( $path_to_secrets . 'secrets.php' );
require_once( $path_to_includes .'validation.php' );
require_once( $path_to_includes . 'snipe_calls.php' );

session_start();

$snipe_id;
$asset_name;

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

// Snipe api call
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin?name=' . $asset_name;

$response = snipe_call( $url, 'POST', $headers );

$json = json_decode($response, true);

// echo snipe errors if any
if ( $json["status"] == "error" ) {
	echo json_encode( array( 'status'=>'error', 'message'=>$json["messages"] ) );
} 
elseif ( $json["status"] == "success" ) {
	echo json_encode( array( 'status'=>'success', 'message'=>'Asset checked in', 'tech'=>$_SESSION['tech_id'] ) );

	// update "Last snipeplus user" field for asset
	$url =  'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '?_snipeit_last_snipeplus_user_13=' . $_SESSION['tech_id'];
	snipe_call( $url, 'PATCH', $headers );
}
else {
	// Should never get here
	echo json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}

?>