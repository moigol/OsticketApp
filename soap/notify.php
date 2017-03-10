<?php
//set integration hash
DEFINE('INTEGRATION_ID', 'da4b9237bacccdf19c0760cab7aec4a8359010b0');//Replace hash tags with your integration ID found on the "Settings" tab in the app

function post_json($json_array, $integration_id = null, $url = "http://atomiccomputers.com/osTicket_api/eTicketAPI.php") {
    if (!is_array($json_array)) {
        return false;
    }
    
    $json_array['integration_id'] = INTEGRATION_ID;
    
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_HEADER, true);
	//curl_setopt($ch, CURLINFO_HEADER_OUT, false);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded; charset=utf-8"));
	//curl_setopt($ch, CURLOPT_USERPWD, 'myusername:mypassword');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json_array);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
    
    if(curl_errno($ch))
    {
        mail("james@atomicx.com", "curl error", print_r(curl_error($ch), true));
    }
    
	curl_close($ch);
    
    if ($result) {
        $result = json_decode($result);
    }
	
	return $result;
}

?>