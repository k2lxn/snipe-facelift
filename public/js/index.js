
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
                window.location.reload( true ) ;
			}
		}
	};

	xmlhttp.open( "POST", url, true ) ;
    xmlhttp.send( params ) ; // params need to be converted to a FormData object??
}


/* DOM ready */
$(document).ready(function() {

	// hook up close buttons for modals
	$(".close").click( function(){
		console.log("closing window");
		$( $(this).data("dismiss") ).css("display", "none");
	});

	// Set default action for Go button
	$("#go").data("action", "checkin");

	// Main actions menu
	$("ul#actions-menu > li").click( function(){

		// Indicate which action is selected	
		$("ul#actions-menu > li.active").removeClass("active");
		$(this).addClass("active");

		// Display relevant form elements
		if ( $(this).data("hide-user") === true ) {
			$("input#user").prop('required',false).parents(".form-row").addClass('d-none');
			$("input#checkin-date").prop('required',false).parents(".form-row").addClass('d-none');
		} else {
			$("input#user").prop('required',true).parents(".form-row").removeClass('d-none');
			$("input#checkin-date").prop('required',true).parents(".form-row").removeClass('d-none');
		}		

		// Bind action to Go button
		$("#go").data("action", $(this).data("action") );
	});


	// Hook up Go button
	$("form").submit( function(){

		var action = $("#go").data("action") ;
		var asset = $("input#asset").val();

		var url = action + ".php?" ;
		
		// grab other data from form
		$("form input:not([type='submit'])").each( function() {
			if ( $(this).val() !== "" ) {
				url += "&" + $(this).attr("name") + "=" + $(this).val() ;
			}
		});

		console.log( "url:" + url );

		
		ajax_call( url, null, function( response ) {
			response = JSON.parse(response);
			console.log(response);

			// Display error messages
			if ( response["status"] === "error" ) {
				$("#message").addClass("error");
				$("#message .modal-body p").text( response["message"] );
				$("#message.modal").css("display", "block");
			}

			// Clear form fields?
		});
		
		
		return false;  // override default submit behavior
	});

});