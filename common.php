<?php
$backend_url = 'https://web.njit.edu/~npm26/%s';
function query_backend($in, $name){
  global $backend_url;
  $curl = curl_init();
  $url = sprintf($backend_url, $name);
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $in
  ));
  $res = curl_exec($curl);
  curl_close($curl);
  return $res;
}
?>