<?php
require_once( 'config.php' );
require_once( $path_to_secrets . 'secrets.php' );


$today = date("Y-m-d");

$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

// Get all assets from Snipe
//$query = 'https://ts.snipe-it.io/api/v1/hardware?limit=200&status=deployed&sort=expected_checkin&order=desc'
$query = 'https://ts.snipe-it.io/api/v1/hardware?sort=expected_checkin&order=desc';
$ch = curl_init($query);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

if ( $json['total'] != 0 ) {
	$assets = $json['rows'];
	$response_data = array();

	for ($x = 0; $x < count($assets); $x++) {
		if ( $assets[$x]['assigned_to'] !== null && $assets[$x]['expected_checkin'] !== null ) {

			$due_date = date( $assets[$x]['expected_checkin']['date'] );

			if ( $today > $due_date ) {
				// add the data to the array to be returned

				if ( $assets[$x]['last_checkout'] == "" ) {
					if ( $assets[$x]['purchase_date'] != null ) {
						$last_checkout = $assets[$x]['purchase_date']['date'];
					} 	
				}
				else {
					$last_checkout = substr($assets[$x]['last_checkout']['datetime'], 0, 10);
				}

				$response_data[] = array( 'asset_tag'=>$assets[$x]['asset_tag'],
										  'snipe_id'=>$assets[$x]['id'],
										  'assignee_snipe_id'=>$assets[$x]['assigned_to']['id'],
										  'assignee_name'=>$assets[$x]['assigned_to']['name'],
										  'assignee_netID'=>$assets[$x]['assigned_to']['username'],
										  //'checked_out_since'=>substr($assets[$x]['last_checkout']['datetime'], 0, 10),
										  'checked_out_since'=>$last_checkout,
										  'expected_checkin'=>$assets[$x]['expected_checkin']['date'],
										  'model'=>$assets[$x]["model"]["name"],
										  'asset_name'=>$assets[$x]["name"]
									);
			}
		}
	}

	echo json_encode( array('status'=>'success', 'data'=>$response_data ) );
	exit(1);
}
else {
	echo json_encode( array('status'=>'error', 'message'=>'Snipe query failed') );
	exit(1);
}


?>