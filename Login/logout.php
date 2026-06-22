<?php
require_once '../includes/session.php';
require_once '../includes/remember.php';

startSecureSession();

deleteCurrentRememberToken();
clearRememberCookie();
destroySecureSession();

header("Location: /proyecto_cava_Noble/Login/login.php");
exit;