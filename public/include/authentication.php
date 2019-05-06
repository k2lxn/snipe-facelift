<?php
require_once( $path_to_secrets . 'secrets.php' );

function sign_in_allowed( $netID ) {
	global $ad_token;

	// check if user is in computingstaff
	$ch = curl_init( "https://ad-api.thayer.dartmouth.edu/api.php?function=user_in_group&netid=\"" . $netID . "\"&group_name=\"computingstaff\"" );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: " . $ad_token) );
	$data = curl_exec($ch);
	curl_close($ch);

	if ( json_decode($data) == true ){
		return true;
	}
	else {
		// check if user is in helpdesktechs
		$ch = curl_init( "https://ad-api.thayer.dartmouth.edu/api.php?function=user_in_group&netid=\"" . $netID . "\"&group_name=\"helpdesktechs\"" );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: " . $ad_token) );
		$data = curl_exec($ch);
		curl_close($ch);

		if ( json_decode($data) == true ){
			return true;
		}
		
		return false;
	}
}

?>