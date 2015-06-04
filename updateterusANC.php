<?php
error_reporting(E_ALL ^ E_NOTICE);
//update terus
// php -e myscript.php -k=windowszone
// $opts = getopt('k:');
// echo $opts['k']; // prints windowszone

//$opts = getopt('k:');
//if(!defined('MICROSITE'))define('MICROSITE', $opts['k']);

include ("configANC.php");
include ("koneksi.php");
include ("function.php");

$rpp = 500;
$tgl = date('Y-m-d H:i:s');

$url = 'https://api.twitter.com/1.1/search/tweets.json?q=%23'.$hashtag.'&result_type=recent&count='.$rpp.'&include_entities=true';

$oauth = new OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
$oauth->disableSSLChecks();
$oauth->setToken($oauth_access_token, $oauth_access_token_secret);

$oauth->fetch($url);
$results = json_decode($oauth->getLastResponse());

foreach($results->statuses as $value){	
	var_dump($value);exit;
	if(cekcreated($value->created_at)){
		$imgprofile = str_replace("_normal","",$value->user->profile_image_url);
		$media_url = isset($value->entities->media[0]->media_url) ? $value->entities->media[0]->media_url : '';
		if(cekurlpictwit($media_url)==false){//cek selain p.twimg.com
			//$url = isset($value->entities->media[0]->expanded_url) ? $value->entities->media[0]->expanded_url : '';
			
			$url = isset($value->entities->media[0]->expanded_url) ? $value->entities->media[0]->expanded_url : $value->entities->urls[0]->expanded_url;
			
			if(cekurlimage($url)){//cek $sumber=='yfrog.com' || $sumber=='instagram.com' || $sumber=='path.com' || $sumber=='twitpic.com' URL yg bisa di graph
				$media_url = urlimage($url);
			}
			else{//bukan image atau url yg bisa di graph
				$media_url='';
			}
		}
		
		$sql = "INSERT INTO ". TBL_HASHTAG ." (id_tweet, from_user_id_str, profile_image_url, created_at, from_user, text, tgl_input, media) VALUES ('".$value->id."','".$value->user->id."','".$imgprofile."','".$value->created_at."','".$value->user->screen_name."','".addslashes($value->text)."','".$tgl."','".$media_url."')";		
		$insert = mysql_query($sql) or die( mysql_error() );
	}
}

//insert TO PHOTOCONTEST
$result2 = mysql_query("SELECT * FROM ". TBL_HASHTAG ." WHERE media != '' AND SUBSTRING(text, instr(text, 'http://t.co'), LENGTH(text)) not like '% %' and SUBSTRING(text, instr(text, 'http://t.co'), LENGTH(text)) <> ' ' AND text NOT LIKE '%RT %' AND text NOT LIKE '% RT %'  AND text NOT LIKE '%\"@%' 
ORDER BY `id` ASC") or die(mysql_error());
while ($row2 = mysql_fetch_array($result2))
{
	$id = $row2["id"];
	$id_tweet = $row2["id_tweet"];
	$from_user = $row2["from_user"];
	$image = $row2["image"];
	$media = $row2["media"];
	$tgl_input = $row2["tgl_input"];
	$text = $row2["text"];

	$result3 = mysql_query("SELECT count(*) as jumlah FROM ". TBL_PHOTO ." WHERE `image` LIKE '".$media."'") or die(mysql_error());
	$row3 = mysql_fetch_array($result3);
	if($row3['jumlah'] == 0){
		mysql_query("insert into ". TBL_PHOTO ." (twitter,pesan,image,is_tweet,post_date) values ('$from_user','$text','$media',1,'".$tgl_input."')") or die(mysql_error());
	}
}

?>