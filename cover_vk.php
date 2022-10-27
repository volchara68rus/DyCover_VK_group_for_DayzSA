<?php
require "config/config.php";

$img_path      = __DIR__ . '/img/bg.jpg'; // обложка
$font          = __DIR__ . '/fonts/a_Albionic.ttf'; //Шрифт

date_default_timezone_set($time_zone);
$time = date("H:i:s");

$image = imageCreateFromJpeg($img_path);
$txt_color = imagecolorallocate($image, 255, 255, 255); //цвет текста

$server_info = query_source($server_query);

$center_w = imagesx($image) / 2;
$center_h = imagesy($image) / 2;

$box = imagettfbbox(15, 0, $font, '' . $time);
$left = $center_w - round(($box[2] - $box[0]) / 2);
$top = $center_h - round(($box[7] - $box[1]) / 2);

$box1 = imagettfbbox(40, 0, $font, "");
$left1 = $center_w - round(($box1[2] - $box1[0]) / 2);
$top1 = $center_h - round(($box1[7] - $box1[1]) / 2);

$box2 = imagettfbbox(55, 0, $font, 'Онлайн ' . $server_info['players'] . '/' . $server_info['playersmax']);
$left2 = $center_w - round(($box2[2] - $box2[0]) / 2);
$top2 = $center_h - round(($box2[7] - $box2[1]) / 2);

$box3 = imagettfbbox(20, 0, $font, 'Сервер оффлайн');
$left3 = $center_w - round(($box3[2] - $box3[0]) / 2);
$top3 = $center_h - round(($box3[7] - $box3[1]) / 2);

@imagettftext($image, 20, 0, $left - 1100, $top - 265, $txt_color, $font, 'Last update ' . $time);
if ($server_info['status'] != 0) {
	@imagettftext($image, 45, 0, $left3 - 1100, $top2 + 50, $txt_color, $font, 'Онлайн ' . $server_info['players'] . '/' . $server_info['playersmax']);
} else {
	@imagettftext($image, 40, 0, $left3, $top3 + 15, $txt_color, $font, 'Сервер оффлайн');
}

imagejpeg($image, 'bg_cover.jpg', 100);
imagedestroy($image);

include_once('vk.class.php');

$vk = new vk($key_access);
$url = $vk->PhotoUploadServer($group_id, $crop_x, $crop_y, $crop_x2, $crop_y2);
$photo = $vk->UploadPhoto($url['upload_url'], 'bg_cover.jpg');
$result = $vk->SavePhoto($photo['hash'], $photo['photo']);

echo "ok";


function query_source($address)
{
	$array = explode(":", $address);

	$server['status'] = 0;
	$server['ip']     = $array[0];
	$server['port']   = $array[1];

	if (!$server['ip'] || !$server['port']) {
		exit("EMPTY OR INVALID ADDRESS");
	}

	$socket = @fsockopen("udp://{$server['ip']}", $server['port'], $errno, $errstr, 1);

	if (!$socket) {
		return $server;
	}

	stream_set_timeout($socket, 1);
	stream_set_blocking($socket, TRUE);
	fwrite($socket, "\xFF\xFF\xFF\xFF\x54Source Engine Query\x00");
	$packet = fread($socket, 4096);
	@fclose($socket);

	if (!$packet) {
		return $server;
	}

	$header                = substr($packet, 0, 4);
	$response_type         = substr($packet, 4, 1);
	$network_version       = ord(substr($packet, 5, 1));

	if ($response_type != "I") {
		exit("NOT A SOURCE SERVER");
	}

	$packet_array          = explode("\x00", substr($packet, 6), 5);
	$server['name']        = $packet_array[0];
	$server['map']         = $packet_array[1];
	$server['game']        = $packet_array[2];
	$server['description'] = $packet_array[3];
	$packet                = $packet_array[4];

	$server['players']     = ord(substr($packet, 2, 1));
	$server['playersmax']  = ord(substr($packet, 3, 1));
	$server['bots']        = ord(substr($packet, 4, 1));
	$server['status']      = 1;
	$server['dedicated']   =     substr($packet, 5, 1);
	$server['os']          =     substr($packet, 6, 1);
	$server['password']    = ord(substr($packet, 7, 1));
	$server['vac']         = ord(substr($packet, 8, 1));

	return $server;
}
