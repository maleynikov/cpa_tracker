<?php

	$data['get']    = $_GET;
	$data['post']   = $_POST;

	$data['get']['txt_param7'] = $_SERVER['HTTP_REFERER'];
	$data['get']['net'] = 'pixel';

	$s_data = serialize($data)."\n";
	if (strlen ($s_data)>0)
	{
	        file_put_contents(_CACHE_PATH.'/postback/.postback_'.date('Y-m-d-H-i'), $s_data, FILE_APPEND | LOCK_EX);		
	}

	$im=imagecreate(1,1);
	imagecolorallocate($im,0,0,0);
	header("Content-type: image/jpeg");
	imagejpeg($im,'',100);

?>