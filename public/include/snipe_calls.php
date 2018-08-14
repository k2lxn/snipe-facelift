<?php


function snipe_call( $url, $method, $headers ) {
	$ch = curl_init( $url );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method );

	$data = curl_exec($ch);
	curl_close($ch);

	return $data ;
}


/*
function checkin( $snipe_id, $headers ) {

	$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

	$data = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($data, true);

	// echo snipe errors if any
	if ( $json["status"] == "error" ) {
		return json_encode( array( 'status'=>'error', 'message'=>$json["messages"] ) );
		//exit(1);
	} 
	else {
		return json_encode( array( 'status'=>'success', 'message'=>'Asset checked in' ) );
	}

	// Should never get here
	return json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}


function checkout( $snipe_id, $assignee_id, $checkout_date, $new_checkin_date, $headers ) {
	$url = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin_date;

	$ch = curl_init( $url );

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

	$data = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($data, true);

	$success_message = "Loan extended to {$new_checkin_date}" ;

	if ( $json["status"] == "success") {
		return json_encode( array('status'=>'success',
								'message'=>$success_message) );
	}
	else {
		return json_encode(array( 'status'=>'error', 'message'=>$json["messages"]));
	}

	// Should never get here
	return json_encode( array( 'status'=>'error', 'message'=>'Something went weirdly wrong' ) );
}
*/

?>