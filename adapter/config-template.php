<?php

// Set FEVER_ROOT to be the root directory of your Fever installation.
define('FEVER_ROOT', "../fever/");

// Fever-Feed Hawk Adapter temporarily disables access when more than 6 subsequent requests 
// are received with the correct email address but incorrect password in the past 24 hours.
// If you accidentally lock yourself out, you can override this by temporarily setting 
// DISABLE_AUTH_LOCK to true.
//
// define('DISABLE_AUTH_LOCK', false);

?>
