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

function fields_to_str($fields){
  $fields_string = "";
  foreach($fields as $key=>$value){
    $fields_string .= $key. '=' .$value. '&';
  }
  rtrim($fields_string, '&');
  return $fields_string;
}

function check_credentials($user, $pass){
    $fail_string = "Unsuccessful login with UCID(%s).";
    $success_string = "Successful login with UCID(%s).";
    $webauth_url = 'https://myhub.njit.edu/vrs/ldapAuthenticateServlet';
    $fields = array(
      "user_name" => urlencode($user),
      "passwd" => urlencode($pass),
      "SUBMIT" => urlencode('Login')
    );
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $webauth_url,
      CURLOPT_POST => count($fields),
      CURLOPT_RETURNTRANSFER => true,
      //CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_POSTFIELDS => fields_to_str($fields)
    ));
    $webauth_response = curl_exec($curl);
    // on success the response will be a redirect, so empty`
    if (empty($webauth_response)){
      return true;
    }
    // otherwise you get the login page backend
    else {
      return false;
    }
}

function query_backend($url){
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    //CURLOPT_VERBOSE => true,
    //CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true
  ));
  $response = curl_exec($curl);
  return $response;
}

function login_response_json($backend_response, $webauth_bool, $ucid){
  $fail_string = sprintf("Unsuccessful login with %s (webnjit).", $ucid);
  $success_string = sprintf("Successful login with %s (webnjit).", $ucid);
  $copy = json_decode($backend_response, true);
  $copy['webnjit']['success'] = $webauth_bool;
  $copy['webnjit']['message'] = ($webauth_bool ? $success_string : $fail_string);
  return json_encode($copy);
}

//input from front end
$frontend_input = file_get_contents('php://input');
$frontend_data = json_decode($frontend_input, true);
$user = $frontend_data['username'];
$pass = $frontend_data['password'];

// for testing on command line
//$user = $argv[1];
//$pass = $argv[2];

$backend_url = 'https://web.njit.edu/~npm26/logingate.php';
$frontend_msg = login_response_json(query_backend($backend_url), check_credentials($user, $pass), $user);
header('Content-Type: application/json');
echo $frontend_msg;

?>