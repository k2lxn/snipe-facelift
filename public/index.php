<?php
require_once( '../CAS-1.3.3/CAS.php' ) ;

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
			//echo "<p> User: " . $username . "</p>" ;
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
    
    <link rel="stylesheet" href="css/index.css" />
</head>

<body>

<h1>Snipe+</h1>

<ul id="actions-menu">
    <li class="active" data-action="checkin" data-hide-user=true>Checkin</li>
    <li data-action="checkout">Checkout</li>
    <li data-action="extend-loan">Extend</li>
</ul>

<form>
    <div class="form-row">
        <label for="asset">Asset</label>
        <input type="text" id="asset" name="asset" required>
    </div>
    <div class="form-row d-none">
        <label for="user">To</label>
        <input type="text" id="user" name="user">
    </div>
    <div class="form-row d-none">
        <label for="checkin-date">Until</label>
        <input type="date" id="checkin-date" name="checkin-date">
    </div>
    <div class="form-row">
        <input type="submit" id="go" value="Go" class="btn btn-primary">
    </div>
</form>

</body>


<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/index.js"></script>


</html>