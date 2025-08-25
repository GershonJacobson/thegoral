<?php
function encrypt_decrypt($action, $string)
{
	$output = false;
	$key = '';

	$iv = md5(md5($key));

	if( $action == 'encrypt' )
	{
		$output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
		$output = base64_encode($output);
	}
	else if( $action == 'decrypt' )
	{
		$output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
		$output = rtrim($output, "");
	}
	return $output;
}

//$a = "0XhLaR5X06W6EGFEbDGHTTzNJ67jhaT2u6OTcjzfkYc=";
//echo encrypt_decrypt('decrypt', $a);
?>