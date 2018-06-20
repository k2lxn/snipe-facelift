<?php
require_once('../secrets.php');
//include('helpers/validation.php');

$target_tag;
$snipe_id;
$assignee_id;
$last_checkout;

if ( isset($_GET['asset']) ){
	$requested_tag = $_GET['asset'] ;

	// do data sanitization, i.e. validate_asset_tag( $_GET['asset'] ) etc
	// But for now:
	$target_tag = $_GET['asset'] ;
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

// 1st curl: search by asset tag
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
		if ( $asset_tag == $target_tag ) {

			$snipe_id = $assets[$x]['id'];	
			$assignee_id = $assets[$x]['assigned_to']['id'];
			$last_checkout = new DateTime($assets[$x]['last_checkout']['datetime']);
			$checkout_date = $last_checkout->format('Y-m-d');
			$expected_checkin = $assets[$x]['expected_checkin']['date'];	
		}
	}		
}

// Check that the asset tag is actually in Snipe
if ( $snipe_id == null ) {
	echo json_encode(array( 'status'=>'error', 'message'=>'No matching asset found'));
	exit(1);
}


// 2nd curl: checkin
$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

// Check that asset hasn't already been checked in (in which case it won't have an assignee to re-checkout to)
if ( $json["status"] == "error" ) {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
	exit(1);
} 


// 3rd curl: checkout

// Check that $assignee_id was properly retrieved from the first curl
if ( $assignee_id == null ) {
	echo json_encode(array('status'=>'error', 'message'=>'Could not find the user to renew this loan for'));
	exit(1);
}

if ( $checkout_date == null ) {
	$checkout_date = date("Y-m-d");
}

if ( $expected_checkin == null ) {
	$expected_checkin = date("Y-m-d");
	//print("expected_checkin appears to be null, assuming extension from today, " . $expected_checkin . " \n");
}	
$new_checkin = date_add( date_create( $expected_checkin ), new DateInterval('P7D') );
$new_checkin = date_format($new_checkin, "Y-m-d") ;
//print("Extending loan to " . $new_checkin );


$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin;

$ch = curl_init( $url );

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);

$success_message = "Loan extended to {$new_checkin}" ;

if ( $json["status"] == "success") {
	echo json_encode( array('status'=>'success',
							'message'=>$success_message) );
}
else {
	echo json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
}

/*
echo json_encode( array('asset_tag'=>$target_tag,
                        'snipe_id'=>$snipe_id,
                        'assignee_id'=>$assignee_id,
                        'checkout_date'=>$checkout_date,
                        'expected_checkin'=>$expected_checkin) ) ;

*/

?>








