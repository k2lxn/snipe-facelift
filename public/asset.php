<?php
require_once('../secrets.php');
require_once('helpers/validation.php');

// Make sure request is good
if ( isset($_GET['asset']) ){
	$target_tag = sanitize_asset_tag( $_GET['asset'] );

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

			// if it's checked out
			if ( $assets[$x]['assigned_to'] != null ) {
				$response_data = array( 'asset_tag'=>$assets[$x]['asset_tag'],
										'snipe_id'=>$snipe_id,
										'assignee_id'=>$assets[$x]['assigned_to']['id'],
										'assignee_name'=>$assets[$x]['assigned_to']['name'],
										'checked_out_since'=>substr($assets[$x]['last_checkout']['datetime'], 0, 10),
										'expected_checkin'=>$assets[$x]['expected_checkin']['date'],
										'model'=>$assets[$x]["model"]["name"]
									);
			}
			// if it's not checked out
			else {
				$response_data = array( 'snipe_id'=>$snipe_id );
			}	
		}
	}		
}

// Check that the asset tag is actually in Snipe
if ( $snipe_id == null ) {
	echo json_encode(array( 'status'=>'error', 'message'=>'Hi Jane. No matching asset found'));
	exit(1);
}

echo json_encode(array( 'status'=>'success', 
						'data'=>$response_data ));



?>