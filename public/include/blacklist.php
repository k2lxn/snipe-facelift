<?php
require_once( $path_to_secrets . 'secrets.php' );
require_once( $path_to_includes .'validation.php' );

/*
if ( isset($_GET['netID']) ){
	$target_netID = sanitize_netID( $_GET['netID'] );

	if ( $target_netID == false ) {
		echo json_encode( array('status'=>'error', 'message'=>'Invalid netID' ) );
		exit(1);
	}
	
}
*/

function blacklisted( $netID ) {
	$ch = curl_init( "https://ad-api.thayer.dartmouth.edu/api.php?function=user_in_group&netid=\"" . $netID . "\"&group_name=\"denylogin\"" );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: " . $ad_token) );
	$data = curl_exec($ch);
	curl_close($ch);

	if ( json_decode($data) == true ){
		return true;
	}
	else {
		return false;
	}
}

?>