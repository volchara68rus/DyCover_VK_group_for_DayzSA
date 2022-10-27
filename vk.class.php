<?php
class VK {
 
    public $token = ''; 
    public function __construct($token) {
        $this->token = $token; 
    }
	public function PhotoUploadServer($group_id,$crop_x,$crop_y,$crop_x2,$crop_y2) {
        $data = array( 
            'group_id'      => $group_id,
            'v'            => '5.131', 
            'crop_x'       => $crop_x,
            'crop_y'       => $crop_y,
            'crop_x2'      => $crop_x2,
            'crop_y2'      => $crop_y2,
        );
        $out = $this->request('https://api.vk.com/method/photos.getOwnerCoverPhotoUploadServer', $data);
        return $out['response'];
	}
	
	public function UploadPhoto($url, $file) {
        $data = array( 
            'photo'      => new CURLFile($file), 
        );
        $out = $this->request($url, $data);
        return $out;
	}
	
	public function SavePhoto($hash, $photo) {
        $data = array( 
			'hash'       => $hash,
            'photo'      => $photo,
			'v'            => '5.131', 
        );
        $out = $this->request('https://api.vk.com/method/photos.saveOwnerCoverPhoto', $data);
        return $out;
	}
     
    public function request($url, $data = array()) {
        $curl = curl_init(); /
         
        $data['access_token'] = $this->token; 
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); 
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         
        $out = json_decode(curl_exec($curl), true); 
         
        curl_close($curl); 
         
        return $out; 
    }
}