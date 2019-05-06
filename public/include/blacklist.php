<?php
require_once( $path_to_secrets . 'secrets.php' );

function blacklisted( $netID ) {
	global $ad_token;

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