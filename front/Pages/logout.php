<?php
session_start(); // Start the session
session_destroy(); // Destroy all session data
header("Location: SignPage.html"); // Redirect to the login page
exit; // Ensure script execution stops
