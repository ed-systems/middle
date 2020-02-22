<?php
include 'db_util.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//input from front end
$frontend_input = file_get_contents('php://input');
$frontend_data = json_decode($frontend_input, true);
$user = $frontend_data['username'];
$pass = $frontend_data['password'];

// validate credentials
$db = new db();
$res = db.check_credentials($user, $pass);

// todo: send json request to backend, get response, send to front end i.e. stdout

// curl stuff
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $json
));
// send and retreive response
$response = curl_exec($curl);
curl_close($curl)