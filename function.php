<?php 
//random char
function generateString ($length = 8)
{
	// mulai dengan string kosong
	$v_string = "";

	// definisikan karakter-karakter yang diperbolehkan
	$possible = "0123456789bcdfghjkmnpqrstvwxyz";

	// set up sebuah counter
	$i = 0;

	// tambahkan karakter acak ke $v_string sampai $length tercapai
	while ($i < $length) {
		// ambil sebuah karakter acak dari beberapa
		// kemungkinan yang sudah ditentukan tadi
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

		// kami tidak ingin karakter ini jika sudah ada pada string
		if (!strstr($v_string, $char)) {
			$v_string .= $char;
			$i++;
		}
	}
	return $v_string;
}

//expand url
function TextAfterTag($input, $tag){
        $result = '';
        $tagPos = strpos($input, $tag);

        if (!($tagPos === false))
        {
                $length = strlen($input);
                $substrLength = $length - $tagPos + 1;
                $result = substr($input, $tagPos + 1, $substrLength); 
        }

        return trim($result);
}
/*
function expandurl($url){
        $format = 'json';
        $api_query = "http://api.longurl.org/v2/expand?" .
                    "url={$url}&response-code=1&format={$format}";
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $api_query );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $fileContents = curl_exec($ch);
        curl_close($ch);
        $s1=str_replace("{"," ","$fileContents");
        $s2=str_replace("}"," ","$s1");
        $s2=trim($s2);
        $s3=array();
        $s3=explode(",",$s2);
        $s4=TextAfterTag($s3[0],(':'));
        $s4=stripslashes($s4);
        $s4=str_replace('"','',$s4);
        return $s4;
 }
*/
function expandurl($url){
	$format = 'json';
	//$api_query = "http://api.longurl.org/v2/expand?url=".$url."&response-code=1&format=".$format;
	$api_query = "http://expandurl.appspot.com/expand?url=".$url;
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $api_query );
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$fileContents = curl_exec($ch);
	curl_close($ch);
	$s1=str_replace("{"," ","$fileContents");
	$s2=str_replace("}"," ","$s1");
	$s2=trim($s2);
	$s3=array();
	$s3=explode(",",$s2);
	$s4=TextAfterTag($s3[1],(':'));
	$s4=stripslashes($s4);
	$s4=str_replace('"','',$s4);
	return $s4;
}
//get url from text
function geturl($text){
	preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $match);
	return  $match[0][0];
}

function file_get_contents_curl($url)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function getimgfrompath($html){
	$html = file_get_contents_curl($html);
	
	//parsing begins here:
	$doc = new DOMDocument();
	@$doc->loadHTML($html);
	$nodes = $doc->getElementsByTagName('title');
	
	//get and display what you need:
	$title = $nodes->item(0)->nodeValue;
	
	$metas = $doc->getElementsByTagName('meta');
	
	for ($i = 0; $i < $metas->length; $i++)
	{
		$meta = $metas->item($i);
	
		if($meta->getAttribute('property') == 'twitter:image')
			$img = $meta->getAttribute('content');
	}
	
	return $img;
}

function cekurlimage($url){
	$pieces = explode("/", $url);
	$sumber = $pieces[2]; //get website source
	if($sumber=='yfrog.com' || $sumber=='instagram.com' || $sumber=='path.com' || $sumber=='twitpic.com'){//option source
		return true;
	}else{
		return false;
	}
}

function cekurlpictwit($url){
	$pieces = explode("/", $url);
	$sumber = $pieces[2]; //get website source
	if($sumber=='pbs.twimg.com'){//option source
		return true;
	}else{
		return false;
	}
}

//save image to directory by cron
function saveimage($url){
	$pieces = explode("/", $url);
	$sumber = $pieces[2]; //get website source "sementara yang baru bisa ke save itu yfrog dan instagram"	
	
	if($sumber=='yfrog.com'){
		$source = $url.':medium';
	}elseif($sumber=='instagram.com'){
		$json = file_get_contents('http://api.instagram.com/oembed?url='.$url.'&client_id=beabbc90a1c34823a27363a0ad0244a2');//INSTAGRAM-------------
		$json = json_decode($json);
		$source = $json->url;
	}elseif($sumber=='path.com'){		
		$source = getimgfrompath($url);
		$source = str_replace('https','http',$source);
	}elseif($sumber=='twitpic.com'){		
		$ids = $pieces[3];
		$source = expandurl('http://twitpic.com/show/full/'.$ids);
	}else{
		$source = $url;
	}
	$exe = explode(".", $source);
	$exten = end($exe);//get extension
	$ext = explode("?", $exten);
	$exten = $ext[0];
	
	$img_filename = date("YmdHis") . "_" . generateString(5);	// new filename
	$img = UPLOAD_PATH. '/'.$img_filename.'.'.$exten; //save to directory
	
	file_put_contents( $img, file_get_contents($source) );
	return $img_filename.'.'.$exten;
}

//save image to directory by user per photo
function saveimageview($url){	
	$exe = explode(".", $url);
	$exten = end($exe);
	$ext = explode("?", $exten);
	$exten = $ext[0];
	$img_filename = date("YmdHis") . "_" . generateString(5);	// new filename
	$img = UPLOAD_PATH. '/'.$img_filename.'.'.$exten; //save to directory

	file_put_contents( $img, file_get_contents($url) );
	return $img_filename.'.'.$exten;
}

//save source img to db
function urlimage($url){
	$pieces = explode("/", $url);
	$sumber = $pieces[2]; //get website source "sementara yang baru bisa ke save itu yfrog dan instagram"
	if($sumber=='yfrog.com'){
		$source = $url.':medium';
	}elseif($sumber=='instagram.com'){
		$json = file_get_contents('http://api.instagram.com/oembed?url='.$url.'&client_id=beabbc90a1c34823a27363a0ad0244a2');//INSTAGRAM-------------
		$json = json_decode($json);
		$source = $json->url;
	}elseif($sumber=='path.com'){
		$source = getimgfrompath($url);
		$source = str_replace('https','http',$source);
	}elseif($sumber=='twitpic.com'){		
		$id = $pieces[3];
		$source = expandurl('http://twitpic.com/show/full/'.$id);
	}else{
		$source = $url;
	}
	return $source;
}

//cek created_at
function cekcreated($created_at){
	$result = mysql_query("SELECT count(*) as jumlah FROM ". TBL_HASHTAG ." WHERE created_at='".$created_at."'") or die(mysql_error());
	$row = mysql_fetch_array($result);
	if($row['jumlah'] > 0){
		return false;
	}else{
		return true;
	}
}
?>