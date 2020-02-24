<?php

function headers_to_array($header_text){
  $headers = array();
  $arrRequests = explode("\r\n\r\n", $header_text);
  for ($index = 0; $index < count($arrRequests) -1; $index++) {
    foreach (explode("\r\n", $arrRequests[$index]) as $i => $line){
      if ($i === 0) {
        $headers[$index]['http_code'] = $line;
      }
      else {
        list ($key, $value) = explode(': ', $line);
        $headers[$index][$key] = $value;
      }
    }
  }
  return $headers;
}

function check_credentials($user, $pass){
    $fail_string = "Unsuccessful login with UCID(%s).";
    $success_string = "Successful login with UCID(%s).";
    $service_url = 'https://webauth.njit.edu:443//idp/profile/cas/login?service=https%3A%2F%2Fportal.njit.edu%2Fc%2Fportal%2Flogin';
    // first send request to get to login page
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $service_url,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true
      //  CURLOPT_VERBOSE => true,
    ));
    // get webauth url from response header
    $service_response = curl_exec($curl);
    $headers = headers_to_array($service_response);
    $webauth_url = sprintf("https://webauth.njit.edu%s", $headers[0]['Location']);
    // now send actual request
    $fields = array(
      "j_username" => urlencode($user),
      "j_password" => urlencode($pass),
      "_eventId_proceed" => ""
    );
    curl_setopt_array($curl, array(
      CURLOPT_URL => $webauth_url,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true,
      //  CURLOPT_VERBOSE => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $fields
    ));
    $webauth_response = curl_exec($curl);
    $webauth_headers = headers_to_array($webauth_response);
    print_r($webauth_headers);
}

function backend_response($url){
   $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_VERBOSE => true,
      CURLOPT_POST => true
    ));
    return curl_exec($url);
}

//input from front end
$frontend_input = file_get_contents('php://input');
$frontend_data = json_decode($frontend_input, true);
//$user = $frontend_data['username'];
//$pass = $frontend_data['password'];
$user = $argv[1];
$pass = $argv[2];

$backend_url = 'https://web.njit.edu/~npm26/logingate.php';

echo check_credentials($user, $pass);
// echo response to stdout (will be forwarded to backend)
?>