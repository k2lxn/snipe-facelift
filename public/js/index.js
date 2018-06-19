
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

	// attach onclicks 
	$("#extend-loan").click( function(){
		console.log("Extending loan");

		var url = "extend-loan.php?asset=TEST" ;

		ajax_call( url, null, function( response ) {
			response = JSON.parse(response);

			console.log("ajax_call worked; response: ");
			console.log(response);
		});

		
	});

});