<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: logout.php
// Purpose: Destroy session data.
session_start();
session_destroy();

header ("Location: index.php"); 

?>