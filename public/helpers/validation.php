<?php

function sanitize_asset_tag( $raw_request ) {
	$asset = strtolower( $raw_request );

	// accept: 4 digits, optionally preceded by "l"
	if ( preg_match('/^l?\d{4}$/', $asset, $matches) ) {
		$sanitized_tag = $matches[0];
		return $sanitized_tag;
	}

	return false;
}


function sanitize_snipe_id( $raw_request ) {
	if ( preg_match( '/^\d+$/', $raw_request, $matches ) ) {
		$sanitized_snipe_id = $matches[0];
		return $sanitized_snipe_id;
	}

	return false;
}


function sanitize_date( $raw_request ) {
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_request, $matches ) ) {
		$sanitized_date = $matches[0];
		$date_arr = explode( '-', $sanitized_date );

		if ( checkdate( $date_arr[1], $date_arr[2], $date_arr[0] ) ) {
			return $sanitized_date;
		}
	} 

	return false;
}

function date_is_in_future( $date_string ) {
	$today = date("Y-m-d");
	if ( $date_string > $today ) {
		return true;
	}

	return false;	
}


?>