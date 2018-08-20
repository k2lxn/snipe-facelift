
function ajax_call( url, params, callback ) {

	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function() {
		if( xmlhttp.readyState==XMLHttpRequest.DONE ) {
			if( xmlhttp.status==200 ) {
				var response = false;
            	try {
            		response = JSON.parse( xmlhttp.responseText ) ;
            	} catch( e ) {
                    // we don't need to do anything
            	}

            	var summon_callback = true ;
            	// check for errors
            	/*if ( typeof(response)=="object" && "success" in response && response["success"] === false ) {
					summon_callback = false;
            	}*/

            	if( callback!=null && summon_callback ) {
                    callback( xmlhttp.responseText ) ;
                }

			}
			// non 200 response
			else {
				// something didn't go right, could be that authentication became required in which case the only way to get it is to do a full reload
               // window.location.reload( true ) ;
			}
		}
	};

	xmlhttp.open( "POST", url, true ) ;
    xmlhttp.send( params ) ; // params need to be converted to a FormData object??
}

var message_timeout;

function hide_message( ms_duration ) {
	// set default timeout wait
	ms_duration = (typeof ms_duration === 'number' ) ? ms_duration : 2600;
	
	clearTimeout(message_timeout);
	message_timeout = setTimeout(function(){
		$("#message").fadeOut();
	}, ms_duration);
}

function display_error( message ) {
	$("#message").removeClass("success").addClass("error");
	$("#message .modal-body p").text( message );
	$("#message.modal").fadeIn( "fast" );
	hide_message();
}

function display_success( message ) {
	$("#message").removeClass("error").addClass("success");
	$("#message .modal-body p").text( message );
	$("#message.modal").fadeIn( "fast" );
	hide_message();
}


function populate_checkin_or_extend( data ) {
	// attach data to hidden fields
	$("form input[name=snipe_id]").val( data["snipe_id"] );
	$("form input[name=assignee_id]").val( data["assignee_id"] );
	$("form input[name=original_checkout_date]").val( data["checked_out_since"] );

	// Display info
	$("#checkin-options .modal-title").html( data["asset_tag"] + " - " + data["model"] );
	$("#checkin-options .user-name").text( data["assignee_name"] );
	
	var currently_due = new Date( data["expected_checkin"] );
	// correct for timezone
	currently_due.setTime( currently_due.getTime() + currently_due.getTimezoneOffset()*60000 );
	var today = new Date();
	
	// expected checkin
	if ( data["expected_checkin"] !== null ) {
		$("#checkin-options .expected-checkin").text( data["expected_checkin"] );
		if ( today > currently_due ) {
			$("#checkin-options .expected-checkin").addClass("overdue");
		}
		else {
			$("#checkin-options .expected-checkin").removeClass("overdue");
		}

	} else {
		$("#checkin-options .expected-checkin").text( "date not set ..." );
	}

	// Set default extension to 1 week beyond current expected checkin
	if ( data["expected_checkin"] !== null ) {
		var default_date = new Date( currently_due );
	}
	else {
		default_date = new Date();
	}
	default_date.setDate( default_date.getDate() + 7 );
	var default_date_string = default_date.toISOString().split('T')[0] ;
	$("form#extend-loan .extend-until").val( default_date_string );
}

function populate_checkout( data ) {
	// Display info
	$("#checkout-options .modal-title").html( data["asset_tag"] + " - " + data["model"] );

	// attach data to hidden fields
	$("form#checkout input[name=snipe_id]").val( data["snipe_id"] );
}


/* DOM ready */
$(document).ready(function() {

	// hook up close buttons for modals
	$(".close").click( function(){
		$( $(this).data("dismiss") ).fadeOut();
	});

	// asset request form
	$("form#get-asset").submit( function() {
		var url = "asset.php?" + $(this).serialize();
		console.log( "url: " + url );

		ajax_call( url, null, function( response ) {
			response = JSON.parse(response);
			console.log(response);

			// Display error messages
			if ( response["status"] === "error" ) {
				$("#message").addClass("error");
				$("#message .modal-body p").text( response["message"] );
				$("#message.modal").css("display", "block");
			}

			// present action options
			else {
				if ( "assignee_id" in response["data"] ) {
					// open checkin/extend form
					populate_checkin_or_extend( response["data"] );
					$("#checkin-options.modal").css("display", "block");
				}	

				else {
					console.log("This asset is available");
					// open checkout form
					populate_checkout( response["data"] );
					$("#checkout-options.modal").css("display", "block");
				}
			}
		});

		return false;
	});


	// checkin form
	$("form#checkin").submit( function() {
		var url = "checkin.php?" + $(this).serialize();
		console.log( "url: " + url );

		ajax_call( url, null, function( response ) {
			response = JSON.parse(response);
			console.log(response);

			// Display error messages
			if ( response["status"] === "error" ) {
				display_error( response["message"] );
			}

			// else, Display success message and close modal window
			else if ( response["status"] === "success" ) {
				display_success( response["message"] );
				$("#checkin-options").fadeOut();
			}
		});	

		return false;
	});


	// extend loan form
	$("form#extend-loan").submit( function() {
		var url = "extend-loan.php?" + $(this).serialize();
		console.log( "url: " + url );

		ajax_call( url, null, function( response ){
			response = JSON.parse(response);
			console.log(response);

			// Display error messages
			if ( response["status"] === "error" ) {
				display_error( response["message"] );
			}

			// else, Display success message and close modal window
			else if ( response["status"] === "success" ) {
				display_success( response["message"] );
				$("#checkin-options").fadeOut();
			}
		});

		return false;
	});


	// checkout form
	$("form#checkout").submit( function(){
		var url = "checkout.php?" + $(this).serialize();
		console.log( "url: " + url );

		ajax_call( url, null, function( response ){
			response = JSON.parse(response);
			console.log(response);

			// Display error messages
			if ( response["status"] === "error" ) {
				display_error( response["message"] );
			}

			// else, Display success message and close modal window
			else if ( response["status"] === "success" ) {
				display_success( response["message"] );
				$("#checkout-options").fadeOut();
			}
		});

		return false;
	});

});





