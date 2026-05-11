<?php
session_start();

session_unset();
session_destroy();

header("Location: /proyecto_cava_Noble/login/login.php");
exit;