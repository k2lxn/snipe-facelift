<?php
require_once('../secrets.php');
require_once('helpers/validation.php');

// want to get: assigned_to, expected_checkin_date

$target_tag;
$snipe_id;
$assignee_id;
$assignee_name;
$expected_checkin;

if ( isset($_GET['asset']) ){
	$requested_tag = $_GET['asset'] ;

	// do data sanitization
	$target_tag = validate_asset_tag( $_GET['asset'] );

	if ( $target_tag == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid asset tag' ) );
		exit(1);
	}
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'No asset tag given' ) );
	exit(1);
}

$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

// 1st curl: search by asset 
$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware?search=' . $target_tag);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

if ( $json['total'] != 0 ) {
	$assets = $json['rows'];
	for ($x = 0; $x < count($assets); $x++) {
		$asset_tag = $assets[$x]['asset_tag'];
		if ( strtolower( $asset_tag ) == $target_tag ) {
			$snipe_id = $assets[$x]['id'];	

			// Check that the asset tag is actually in Snipe
			if ( $snipe_id == null ) {
				echo json_encode(array( 'status'=>'error', 'message'=>'No matching asset found'));
				exit(1);
			}

			// if it's checked out
			if ( $assets[$x]['assigned_to'] != null ) {
				$response_data = array( 'snipe_id'=>$snipe_id,
										'assignee_id'=>$assets[$x]['assigned_to']['id'],
										'assignee_name'=>$assets[$x]['assigned_to']['name'],
										'checked_out_since'=>new DateTime($assets[$x]['last_checkout']['datetime']),
										'expected_checkin'=>$assets[$x]['expected_checkin']['date']);
			}
			// if it's not checked out
			else {
				$response_data = array( 'snipe_id'=>$snipe_id );
			}	
		}
	}		
}


echo json_encode(array( 'status'=>'success', 
						'response'=>$response_data ));



?>