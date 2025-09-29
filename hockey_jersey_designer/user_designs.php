<?php
require_once 'users/init.php';
if (!$user->isLoggedIn()) {
    Redirect::to('users/login.php');
}

// Show user's saved designs and order history
?>