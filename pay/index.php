<?php

# Payment redirect
# Rootnode http://rootnode.net
#
# Copyright (C) 2011 Marcin Hlybin
# All rights reserved.

include("config.inc.php");

if(!isset($_GET['id']) || !preg_match('/^[a-z0-9]+$/',$_GET['id'])) {
	die("Incorrect URL. Please use 'satan account pay' command.");
}

$dbh = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if(!$dbh) {
        die("Cannot connect to database!");
}

mysql_select_db(DB_NAME, $dbh);

$user_query = mysql_query("
	SELECT id, login, amount, first_name, last_name, mail, lang
	FROM user
	WHERE id='".mysql_real_escape_string($_GET['id'])."'"
);
if(!$user_query) {
        die("Invalid query: ".mysql_error());
}

$user = mysql_fetch_array($user_query);

if(empty($user)) {
	die("Incorrect URL. Please use 'satan account pay' command.");
}

if($user['lang'] != 'en' && $user['lang'] != 'pl') {
	$user['lang'] = 'en';
}

$ts = time();
$client_ip = $_SERVER['REMOTE_ADDR'];
$session_id = $user['login'].'_'.$ts.'_'.$user['lang'];

$sig = md5(PAYU_POS_ID
     . $session_id
     . PAYU_POS_AUTH_KEY
     . $user['amount']
     . $user['login']
     . $user['first_name']
     . $user['last_name']
     . $user['mail']
     . $user['lang']
     . $client_ip
     . $ts
     . PAYU_KEY_SEND
);

$url = "https://www.platnosci.pl/paygw/UTF/NewPayment"
     . "?pos_id="      . PAYU_POS_ID
     . "&pos_auth_key=". PAYU_POS_AUTH_KEY
     . "&session_id="  . $session_id
     . "&amount="      . $user['amount']
     . "&desc="        . $user['login']
     . "&first_name="  . $user['first_name']
     . "&last_name="   . $user['last_name']
     . "&email="       . $user['mail']
     . "&client_ip="   . $client_ip
     . "&language="    . $user['lang']
     . "&ts="          . $ts
     . "&sig="         . $sig;

header("Location: $url");
?>