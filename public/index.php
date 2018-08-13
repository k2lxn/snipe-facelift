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

<div id="message" class="modal" role="dialog">
    <div class="modal-content">
        <div class="modal-header border-0">
            <button type="button" class="close" data-dismiss="#message" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>Test content</p>
        </div>
    </div>
</div>

<form id="get-asset">
    <div class="form-row">
        <label for="asset" class="col-1 col-form-label">Asset</label>
        <div class="col-6">
            <input type="text" id="asset" name="asset" class="form-control" required>
        </div>
        <div class="col-1">
            <input type="submit" value="Go" class="btn btn-primary" data-action="asset">
        </div>
    </div>
</form>

<div id="checkin-options" class="modal" role="">
    <div class="modal-content">
        <div class="modal-header border-0">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="#checkin-options" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>Assigned to <span class="user-name"></span></p>
            <!--<p>Due back <span class="expected-checkin"></span></p>-->
            <form id="checkin">
                <input name='snipe_id' type='hidden'>
                <label for="check_in" class="col-form-label"><p>Due back <span class="expected-checkin"></span></p></label>
                <input type="submit" name="check_in" value="Check in" class="btn btn-primary">
            </form>
            <form id="extend-loan">
                <input name='snipe_id' type='hidden'>
                <input name='assignee_id' type='hidden'>
                <input name='original_checkout_date' type='hidden'>
                <label for="new_checkin_date">Extend until</label>
                <input type="date" class="extend-until" name="new_checkin_date" class="form-control">
                <input type="submit" value="Extend" class="btn btn-primary">
            </form>
        </div>
    </div>
</div>

<div id="checkout-options" class="modal" role="">
    <div class="modal-content">
        <div class="modal-header border-0">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="#checkout-options" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>This asset is available for checkout</p>
            <form id="checkout">
                <input name='snipe_id' type='hidden'>
                <div class="form-row">
                    <div class="col-form-label">
                        <label for="netID">To</label>
                    </div>
                    <div class="col-6">    
                        <input type="text" name="netID" class="form-control" placeholder="netID" required>
                    </div>
                </div> 
                <div class="form-row">   
                    <label for="expected_checkin" class="col-form-label">Until</label>
                    <input type="date" class="extend-until" name="expected_checkin" class="form-control" required>
                    <input type="submit" value="Checkout" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>


</body>


<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/index.js"></script>


</html>

