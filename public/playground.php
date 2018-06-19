
<?php
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
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Snipe API playground</title>
	<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
    
    <link rel="stylesheet" href="css/style.css" />
</head>


<h1>Snipe!</h1>


<?php
include('secrets.php');
	
$access_token = $dev_token;
$headers = array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$access_token,
);

/*
$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware?limit=1500');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, 'YourScript/0.1 (contact@email)');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data, true);
$assets = $json['rows'];


$loaners = [];
for ($x = 0; $x < count($assets); $x++) {
	//echo "\n".'- Name: '.$assets[$x]['name'].', Model: '.$assets[$x]['model']['name'];
	$machine_function = $assets[$x]['custom_fields']['Machine function']['value'];
	if ( $machine_function == "Short term loaner" ) {
		$loaners[] = $assets[$x];
		echo "\n".'<p> - '.$assets[$x]['name'].' - ' . $assets[$x]['model']['name'] . '</p>';
	}
} 

echo "\n".'There are '.count($loaners).' short term loaners'."\n";

echo "\n";
*/


echo "<h1>Grand checkout experiment</h1>";

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



		echo  "\n".'<p>'.$assets[$x]['asset_tag'].' - assigned to ' . $assets[$x]['assigned_to']['name'] . '</p>';

		echo "<p> - snipe_id: " . $snipe_id . "</p>";
		echo "<p> - assignee_id: " . $assignee_id . "</p>";
		echo "<p> - checkout_date: " . $checkout_date . "</p>";
		echo "<p> - expected_checkin: " . $expected_checkin . "</p>";

	}
}	

// checkin
$url_to_curl = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin' ;
echo "<p>curling: " . $url_to_curl ."</p>";
echo "<br><br>";

$ch = curl_init('https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
print_r($ch);
$data = curl_exec($ch);
print("Checkin response \n");
print_r($data);
curl_close($ch);


// re-checkout (extend one week)
if ( $expected_checkin != null ) {
	// checkin based on previous checkin_date
	//echo "<p>Current checkin: " . $expected_checkin . "</p>";
	print("expected_checkin appears to be set");
}
else {
	// checkin based on today's date
	$expected_checkin = date("Y-m-d");
	//echo "<p>No checkin set, assuming extension from today, " . $expected_checkin . "</p>";
	print("expected_checkin appears to be null, assuming extension from today");
}

//$new_checkin = date_create( $expected_checkin );
//date_add( $new_checkin, new DateInterval('P7D'));
$new_checkin = date_add( date_create( $expected_checkin ), new DateInterval('P7D') );
print("new_checkin preformatting");
print_r($new_checkin);
$new_checkin = date_format($new_checkin, "Y-m-d") ;
//echo "<p>Update checkin to " . $new_checkin . "</p>" ;
print("new_checkin formatted");
print_r($new_checkin);

$url_to_curl = 'https://ts.snipe-it.io/api/v1/hardware/' . $snipe_id . '/checkout?checkout_to_type=user&assigned_user=' . $assignee_id . '&checkout_at=' . $checkout_date  . '&expected_checkin=' . $new_checkin;

echo "<p>curling: " . $url_to_curl ."</p>";

$ch = curl_init( $url_to_curl );

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
print_r($ch);
$data = curl_exec($ch);
print("Checkout reponse \n");
print_r($data);
curl_close($ch);


?>



</body>

<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/index.js"></script>

</html>
