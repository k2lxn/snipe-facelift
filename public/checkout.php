<?php
require_once( 'config.php' );
require_once( $path_to_secrets . 'secrets.php' );
require_once( $path_to_includes .'validation.php' );
require_once( $path_to_includes . 'snipe_calls.php' );

session_start();

$snipe_id;
$target_netID;
$assignee_id;
$assignee_name;
$expected_checkin;

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
if ( isset($_GET['netID']) ){
	$target_netID = sanitize_netID( $_GET['netID'] );

	if ( $target_netID == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid netID' ) );
		exit(1);
	}
}

// convert netID to assigneeID
$url = 'https://ts.snipe-it.io/api/v1/users?search=' . $target_netID ;

$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$results = snipe_call( $url, 'GET', $headers);
$json = json_decode($results, true);

if ( $json['total'] != 0 ) {
	$users = $json['rows'];
	for ($x = 0; $x < count($users); $x++) {
		$netID = $users[$x]['username'];

		if ( strtolower( $netID ) == strtolower( $target_netID ) ) {
			$assignee_id = $users[$x]['id'] ;
			$assignee_name = $users[$x]['name'] ;
		}
	}	
}

if ( $assignee_id == null ) {
	echo json_encode(array( 'status'=>'error', 'message'=>'No matching user found'));
	exit(1);
}


// date string validation
if ( isset($_GET['expected_checkin']) ){
	$expected_checkin = sanitize_date( $_GET['expected_checkin'] );
	
	if ( $expected_checkin == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid expected checkin date given. Use format YYYY-MM-DD.' ) );
		exit(1);
	}

	if ( !date_is_in_future( $expected_checkin ) ) {
		echo json_encode( array('status'=>'error', 'message'=>'Expected checkin date must be in the future.' ) );
		exit(1);
	}
}

$checkout_date = date("Y-m-d");


// Snipe api call
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $expected_checkin ;

$response = snipe_call( $url, 'POST', $headers );
$json = json_decode($response, true);

$success_message = "Asset checked out to {$assignee_name} until {$expected_checkin}" ;

if ( $json["status"] == "error" ) {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
}
elseif (  $json["status"] == "success"  ) {
	echo json_encode( array('status'=>'success', 'message'=>$success_message, 'tech'=>$_SESSION['tech_id'] ) );
}
else {
	// Should never get here
	echo json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}


?>