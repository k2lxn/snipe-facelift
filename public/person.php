<?php
require_once( 'config.php' );
require_once( $path_to_secrets . 'secrets.php' );
require_once( $path_to_includes . 'validation.php' );
require_once( $path_to_includes . 'snipe_calls.php' );

// Make sure request is good
if ( isset($_GET['person']) ){
	$request = $_GET['person'] ;

	$search_term = sanitize_netID( $request ) ? sanitize_netID( $request ) : sanitize_name( $request ) ;

	if ( $search_term == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Please provide a first and last name or a netID' ) );
		exit(1);
	}

	//echo json_encode( array('status'=>'cool', 'message'=>$search_term) );
	//exit(1);

}
else {
	echo json_encode( array('status'=>'error', 'message'=>'No name or netID given' ) );
	exit(1);
}

$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

// 1st curl: get user's snipe_id 
$ch = curl_init('https://ts.snipe-it.io/api/v1/users?search=' . $search_term);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$user_data = curl_exec($ch);
curl_close($ch);

$user_json = json_decode($user_data, true);

if ( $user_json['total'] != 0 ) {
	$users = $user_json['rows'];

	// grab snipe_id ($results[0]['id']) and ping api/users/[id]/assets
	$user_snipe_id = $users[0]['id'] ;
	$user_fullname = $users[0]['name'];

	// 2nd curl: get user's assets
	$ch = curl_init('https://ts.snipe-it.io/api/v1/users/' . $user_snipe_id . '/assets');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$asset_data = curl_exec($ch);
	curl_close($ch);

	$asset_json = json_decode($asset_data, true);

	if ( $asset_json['total'] != 0 ) {
		$assets = $asset_json['rows'];

		$response_data = array( 'user_id'=>$user_snipe_id, 'user_name'=>$user_fullname );
		/*
		if ( $assets[$x]['last_checkout'] == "" ) {
			$last_checkout = '2017-12-06' ;
		}
		else {
			$last_checkout = substr($assets[$x]['last_checkout']['datetime'], 0, 10);
		}
		*/

		$last_checkout = substr($assets[$x]['last_checkout']['datetime'], 0, 10);

		for ($x = 0; $x < count($assets); $x++) {	
			$response_data["assets"][] = array( 'asset_tag'=>$assets[$x]['asset_tag'],
										'snipe_id'=>$assets[$x]['id'],
										//'assignee_id'=>$user_snipe_id,
										//'assignee_name'=>$user_fullname,
										'checked_out_since'=>substr($assets[$x]['last_checkout']['datetime'], 0, 10),
										'expected_checkin'=>$assets[$x]['expected_checkin']['date'],
										'model'=>$assets[$x]["model"]["name"],
										'asset_name'=>$assets[$x]["name"]
									);			
		}

		echo json_encode( array('status'=>'success', 'data'=>$response_data) );
		exit(1);	
	}
	else {
		echo json_encode( array('status'=>'error', 'message'=>$user_fullname . ' has no assets assigned') );
		exit(1);
	}
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'Nobody by that name in here') );
	exit(1);
}


?>