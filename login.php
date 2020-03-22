<?php
include 'common.php';
echo query_backend(file_get_contents('php://input'), basename(__FILE__));
?>