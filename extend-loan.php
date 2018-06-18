<?php
require_once('secrets.php');
require_once( 'CAS-1.3.3/CAS.php' ) ;


// initialize phpCAS
phpCAS::client( CAS_VERSION_2_0, 'login.dartmouth.edu', 443, 'cas' ) ;
        #phpCAS::client( CAS_VERSION_2_0, 'login-preprod.dartmouth.edu', 443, '/cas' ) ;

        // no SSL validation for the CAS server
        phpCAS::setNoCasServerValidation();

        // force CAS authentication
        phpCAS::forceAuthentication();

        // at this point, the user has been authenticated by the CAS server
        if( substr_count(phpCAS::getUser(), '@DARTMOUTH.EDU')==1 ) {
            if( !isset($_SESSION['username']) ) {
                $username = phpCAS::getAttribute( 'netid' ) ;
 				echo "<p> Username: " . $username . "</p>" ;
            }
        } else {
            echo "Sorry, you are not in the dartmouth.edu realm." ;
            exit( 1 ) ;
        }
	
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

// 1st curl: search by asset tag
$target_tag = "TEST";
$snipe_id;
$assignee_id;
$last_checkout;

$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware?search=' . $target_tag);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);
$assets = $json['rows'];
for ($x = 0; $x < count($assets); $x++) {
	$asset_tag = $assets[$x]['asset_tag'];
	if ( $asset_tag == $target_tag ) {

		$snipe_id = $assets[$x]['id'];	
		$assignee_id = $assets[$x]['assigned_to']['id'];
		$last_checkout = new DateTime($assets[$x]['last_checkout']['datetime']);
		$checkout_date = $last_checkout->format('Y-m-d');
		$expected_checkin = $assets[$x]['expected_checkin']['date'];	

		print( $assets[$x]['asset_tag'].' - assigned to ' . $assets[$x]['assigned_to']['name'] . "\n" );
		print( "- snipe_id: " . $snipe_id . "\n" );
		print( "- assignee_id: " . $assignee_id . "\n" );
		print( "- checkout_date: " . $checkout_date . "\n" );
		print( "- expected_checkin: " . $expected_checkin . "\n" );
	}
}	



// 2nd curl: checkin
$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
print("Checkin response: \n");
print_r($data);
print("\n");

curl_close($ch);



// 3rd curl: checkout
if ( $expected_checkin == null ) {
	$expected_checkin = date("Y-m-d");
	print("expected_checkin appears to be null, assuming extension from today, " . $expected_checkin . " \n");
}	

$new_checkin = date_add( date_create( $expected_checkin ), new DateInterval('P7D') );
$new_checkin = date_format($new_checkin, "Y-m-d") ;
print("Extending loan to " . $new_checkin );

$url_to_curl = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin;
print( "curling: " . $url_to_curl . "\n");

$ch = curl_init( $url_to_curl );

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

$data = curl_exec($ch);
print("Checkout reponse \n");
print_r($data);
curl_close($ch);

?>








