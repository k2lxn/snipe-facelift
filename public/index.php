<?php
require_once( 'config.php' ) ;

session_start();

if( !isset($_SESSION['tech_id']) ) {
    require_once( $path_to_secrets . 'CAS-1.3.3/CAS.php' ) ;
    require_once( $path_to_secrets . 'allowed-users.php' ) ;

    // initialize phpCAS
    phpCAS::client( CAS_VERSION_2_0, 'login.dartmouth.edu', 443, 'cas' ) ;
    #phpCAS::client( CAS_VERSION_2_0, 'login-preprod.dartmouth.edu', 443, '/cas' ) ;

    // no SSL validation for the CAS server
    phpCAS::setNoCasServerValidation();

    // force CAS authentication
    phpCAS::forceAuthentication();

    // at this point, the user has been authenticated by the CAS server
    if( substr_count(phpCAS::getUser(), '@DARTMOUTH.EDU')==1 ) {
    
        //$_SESSION['tech_id'] = phpCAS::getAttribute( 'netid' ) ;
        $tech_id = phpCAS::getAttribute( 'netid' ) ;
        if ( !in_array($tech_id, $allowed_users) ) {
            echo "Sorry, you don't have permission to use this tool" ;
            exit( 0 ) ;
        } else {
            $_SESSION['tech_id'] = $tech_id;
        }
    }
    else {
        echo "Sorry, you are not in the dartmouth.edu realm." ;
        exit( 0 ) ;
    }
} 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Snipe+</title>
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
    
    <link rel="stylesheet" href="css/index.css" />

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

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
    <label for="asset" class="col-form-label">Asset</label>
    <input type="text" id="asset" name="asset" class="form-control" required>
    <input type="submit" value="Go" class="btn btn-primary">
    </div>
</form>

<form id="get-person">
    <div class="form-row">
    <label for="person" class="col-form-label">Person</label>
    <input type="text" id="person" name="person" class="form-control" required>
    <input type="submit" value="Go" class="btn btn-primary">
    </div>
</form>


<!-- CHECK IN -->
<div id="checkin-options" class="hidden" role="">
    <button type="button" class="close" data-dismiss="#checkin-options" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>

    <h2>Assigned to <span class="user-name"></span>:</h2>

    <form id="assigned-assets">
        <input name='assignee_id' type='hidden'>  
        <ul> </ul> 

        <!-- li template -->
        <script id="asset-listing" type="text/template">
            <li>
                <input type="checkbox" name="asset_id" value="{{snipe_id}}" data-expected_checkin="{{expected_checkin}}" data-original-checkout-date="{{checked_out_since}}" data-asset_name="{{asset_name}}">
                {{asset_tag}}, {{model}} - Due {{expected_checkin}}
            </li>
        </script> 
        
    </form>

    <button id="checkin" class="btn btn-primary">Check in</button>
    <span>&nbsp;or&nbsp;</span>

    <button id="extend-loan" class="btn btn-primary">Extend</button>
    <span>&nbsp;until</span>
    <input type="date" class="extend-until" name="new_checkin_date" class="form-control">

</div>


<!-- CHECKOUT -->

<div id="checkout-options" class="hidden" role="">
    <button type="button" class="close" data-dismiss="#checkout-options" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>

    <h2></h2>
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


</body>


<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/index.js"></script>


</html>

