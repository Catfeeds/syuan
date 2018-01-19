<?php
namespace User\Controller;
use Common\Controller\HomebaseController;
class PublicController extends HomebaseController {
    
	function avatar() {
		$id=I("get.id", 0, "intval");
		$find_user = M('Users')->field('avatar')->where(array("id"=>$id))->find();
		$avatar = '';
		if($find_user['avatar']) {
			$avatar = $find_user['avatar'];
		}
		$show_default=false;
		
		if(empty($avatar)){
			$show_default=true;
		} else {
			if(strpos($avatar,"http")===0){
				header("Location: $avatar");
				return false;
			} else {
				$avatar_dir=C("UPLOADPATH")."avatar/";
				$avatar=$avatar_dir.$avatar;
				if(file_exists($avatar)){
					$imageInfo = getimagesize($avatar);
					if($imageInfo !== false) {
						$mime = $imageInfo['mime'];
						header("Content-type: $mime");
						echo file_get_contents($avatar);
					} else {
						$show_default=true;
					}
				} else {
					$show_default=true;
				}
			}			
		}
		if($show_default){
			$imageInfo = getimagesize("public/images/headicon.png");
			if ($imageInfo !== false) {
				$mime=$imageInfo['mime'];
				header("Content-type: $mime");
				echo file_get_contents("public/images/headicon.png");
			}
		}
		return false;
	}
}
