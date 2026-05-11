<?php
session_start();

unset($_SESSION['carrito']);

header('Location: /proyecto_cava_Noble/carrito.php');
exit;