<?php

function validate_asset_tag( $raw_request ) {
	$asset = strtolower( $raw_request );

	// accept: 4 digits, optionally preceded by "l"
	if ( preg_match('/^l?\d{4}$/', $asset, $matches) ) {
		$valid_tag = $matches[0];
		return $valid_tag;
	}
	else {
		return false;
	}
}


function validate_snipe_id( $raw_request ) {
	if ( preg_match( '/^\d+$/', $raw_request, $matches ) ) {
		$valid_snipe_id = $matches[0];
		return $valid_snipe_id;
	}
	else {
		return false;
	}
}

?>