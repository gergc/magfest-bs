<?php

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: database.php
// Purpose: Sets up some global functions and static config options

//Where is the directory path accessable to this server (WITH VHOST INTACT AND TRAILING /)
$path = 'http://quasared.net/magfest/';
$_SESSION['path'] = $path;
//$_SESSION[''] = $;

//Version
$moddate = '12/10/2010';

//Returns the type of badge based on the badge number.
function calcBadgeType($badgeid)
{
	$badgetype = 'Unknown';
	if($badgeid<=299) $badgetype = 'Staff';
	else if($badgeid<=399) $badgetype = 'Super-Supporter';
	else if($badgeid<=499) $badgetype = 'Supporter';
	else if($badgeid<=649) $badgetype = 'Guest';
	else if($badgeid<=899) $badgetype = 'Dealer';
	else if($badgeid<=2999) $badgetype = 'Attendee';
	else if($badgeid<=5249) $badgetype = 'One-Day Attendee';
	return $badgetype;
}
function calcShortBadgeType($badgeid)
{
	$badgetype = 'Unknown';
	if($badgeid<=299) $badgetype = 'Staff';
	else if($badgeid<=399) $badgetype = 'S-Support';
	else if($badgeid<=499) $badgetype = 'Supporter';
	else if($badgeid<=649) $badgetype = 'Guest';
	else if($badgeid<=899) $badgetype = 'Dealer';
    else if($badgeid<=2999) $badgetype = 'Attendee';
	else if($badgeid<=5249) $badgetype = 'One Day';
	return $badgetype;
}
//MySQL DB info
function connectDB(){
    $conn = mysql_connect("","","") or die(mysql_error()); //removed connection info
    mysql_select_db('m9bs',$conn) or die(mysql_error());
	return $conn;
}
function connectUber(){
	$uberconn = mysql_connect("","","") or die(mysql_error());
	mysql_select_db('m9',$uberconn) or die(mysql_error());
	return $uberconn; 
}
function socket_read_normal($socket, $end=array("\r", "\n")){
    if(is_array($end)){
        foreach($end as $k=>$v){
            $end[$k]=$v{0};
        }
        $string='';
        while(TRUE){
            $char=socket_read($socket,1);
            $string.=$char;
            foreach($end as $k=>$v){
                if($char==$v){
                    return $string;
                }
            }
        }
    }else{
        $endr=str_split($end);
        $try=count($endr);
        $string='';
        while(TRUE){
            $ver=0;
            foreach($endr as $k=>$v){
                $char=socket_read($socket,1);
                $string.=$char;
                if($char==$v){
                    $ver++;
                }else{
                    break;
                }
                if($ver==$try){
                    return $string;
                }
            }
        }
    }
}
function do_post_request($url, $data, $optional_headers = null)
  {
     $params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
               ));
     if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
     }
     $ctx = stream_context_create($params);
     $fp = @fopen($url, 'rb', false, $ctx);
     if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
     }
     $response = @stream_get_contents($fp);
     if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
     }
     return $response;
  }
?>
