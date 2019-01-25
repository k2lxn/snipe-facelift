
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
	// clear any previous results
	$("#assigned-assets ul").html(""); 

	// hidden fields
	$("#checkin-options input[name=assignee_id]").val( data["user_id"] );

	// display name
	$("#checkin-options .user-name").text( data["user_name"] );

	//var listing_template = document.getElementById("asset-listing").innerHTML;
	//var table_header = document.getElementById("asset-table-header").innerHTML;

	if ( "assets" in data ) {
		var listing_template = document.getElementById("asset-listing").innerHTML;
		var table_header = document.getElementById("asset-table-header").innerHTML;
		var latest_due = new Date();

		$("#assigned-assets ul").append( table_header ) ;

		data["assets"].forEach( function( asset ){
			var due_date = asset["expected_checkin"] !== null ? asset["expected_checkin"] : "date not set" ;

			var days_checked_out = Math.round( ( new Date().getTime() - new Date(asset["checked_out_since"]).getTime() ) / (1000*60*60*24) );
			
			if ( days_checked_out > 365 ) {
				var years = Math.round(days_checked_out / 365 );
				var days = days_checked_out % 365 ;
				var ys = years > 1 ? "s" : "" ;
				var ds = days > 1 ? "s" : "" ;

				days_checked_out = `${years} year${ys}, ${days} day${ds}`;
			}
			else {
				var ds = days_checked_out > 1 ? "s" : "" ;
				days_checked_out = `${days_checked_out} day${ds}`;
			}
			
			var listing = listing_template.replace(/{{snipe_id}}/g, asset["snipe_id"])
										//.replace(/{{asset_tag}}/g, asset["asset_tag"])
										.replace(/{{model}}/g, asset["model"]) 
										.replace(/{{checked_out_since}}/g, asset["checked_out_since"])
										.replace(/{{expected_checkin}}/g, due_date)
										.replace(/{{asset_name}}/g, asset["asset_name"])
										//.replace(/{{years_checked_out}}/g, years_checked_out)
										.replace(/{{days_checked_out}}/g, days_checked_out);

			if ( asset["asset_name"] === "" ) {
				listing = listing.replace( /{{asset_tag}}/g, asset["asset_tag"] );
			}
			else if ( asset["asset_name"] ) {
				listing = listing.replace( /{{asset_tag}}/g, asset["asset_name"] );
			}

			$("#assigned-assets ul").append( listing ) ;

			// Indicate if asset is overdue
			if ( new Date(due_date) < new Date() ) {
				$("#assigned-assets .due-date:last-of-type").addClass("overdue");
			}

			// if expected_checkin > latest_due, latest_due = expected_checkin 
			if ( new Date(due_date) > new Date(latest_due) ) {
				latest_due = due_date ;
			}

			// If only one asset returned, check it by default
			if ( data["assets"].length === 1 ) {
				$("#assigned-assets input[type='checkbox']")[0].checked = true;
			}
		});

		// set default datepicker date to latest_due
		var default_date = new Date( latest_due );
		default_date.setDate( default_date.getDate() + 7 );
		var default_date_string = default_date.toISOString().split('T')[0] ;
		$("#checkin-options .extend-until").val( default_date_string );

	}
}

function populate_checkout( data ) {
	// Display info
	var display_name = data["asset_tag"];
	if ( data["asset_name"] !== "") {
		display_name = data["asset_name"];
	}
	$("#checkout-options h2").html( display_name + " - " + data["model"] );

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
				if ( "user_id" in response["data"] ) {
					// open checkin/extend form
					populate_checkin_or_extend( response["data"] );
					$("#checkin-options").fadeIn();
				}	

				else {
					console.log("This asset is available");
					// open checkout form
					populate_checkout( response["data"] );
					$("#checkout-options").fadeIn();
				}
			}
		});

		return false;
	});


	// person request form
	$("form#get-person").submit( function() {
		var url = "person.php?" + $(this).serialize();
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
				populate_checkin_or_extend( response["data"] );
				$("#checkin-options").fadeIn();
			}
			
		});

		return false;
	});

	
	$("#checkin").click( function(){
		// call checkin once for each selected asset
		$("#assigned-assets input[type='checkbox']").each( function() { 
			if ( this.checked === true ) {
				console.log( $(this).data() );
				console.log("asset_name: " + $(this).data("asset_name") );

				//var url = "checkin.php?snipe_id=" + $(this).val() + "&asset_name=" + $(this).data("asset_name") ;
				var url = "checkin.php?snipe_id=" + $(this).val();

				if ( $(this).data("asset_name") !== "" ) {
					url += "&asset_name=" + $(this).data("asset_name") ;
				}
				console.log( url );

				ajax_call( url, null, function( response ) {
					response = JSON.parse(response);
					console.log(response);

					// Display error messages
					if ( response["status"] === "error" ) {
						display_error( response["message"] );
					}

					// else, Display success message and hide #checkin-options
					else if ( response["status"] === "success" ) {
						display_success( response["message"] );
						$("#checkin-options").fadeOut();
					}
				});	

			}
		});
	});


	$("#extend-loan").click( function() {
		// need: $snipe_id; $assignee_id; $checkout_date; $new_checkin_date;
		console.log( $("#assigned-assets").serialize() );

		$("#assigned-assets input[type='checkbox']").each( function() { 
			if ( this.checked === true ){
				//var url = "extend-loan.php?snipe_id=" + $(this).val() + "&assignee_id=" + $("#assigned-assets input[name='assignee_id']").val() + "&checkout_date=" + $(this).data("original-checkout-date") + "&new_checkin_date=" + $("input[name='new_checkin_date']").val() + "&asset_name=" + $(this).data("asset_name"); // ADD DATE DATA
				var url = "extend-loan.php?snipe_id=" + $(this).val() + "&assignee_id=" + $("#assigned-assets input[name='assignee_id']").val() + "&checkout_date=" + $(this).data("original-checkout-date") + "&new_checkin_date=" + $("input[name='new_checkin_date']").val(); // ADD DATE DATA
				if ( $(this).data("asset_name") !== "" ) {
					url += "&asset_name=" + $(this).data("asset_name");
				}

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
			}
		});

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





