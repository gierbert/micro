<?php
namespace hsC;
class getAiDraw{
	public function txt2img(){
		if(empty($_POST['promptTags'])){exit(jsonCode('error1', 'promptTags error ...'));}
		if(empty($_POST['negativeTags'])){exit(jsonCode('error2', 'negativeTags error ...'));}
		$url = AI_DRAW_TXT2IMG;
		$url2 = AI_DRAW_OPTIONS;
		$post_data = array(
		  "prompt" => $_POST['promptTags'],
		  "negative_prompt" => $_POST['negativeTags'],
		  "steps" =>20
		);
		$models = array(
		  "sd_model_checkpoint" => $_POST['models'],
		  "CLIP_stop_at_last_layers" => 2
		);
		$options = send_post($url2, $models);
		
		$draw = send_post($url, $post_data);
		$draw = json_decode($draw,true);
		$result = base64_image_content($draw['images'][0]);
		if($result == 'error'){
			jsonCode('error3', 'imageUrl error ...');
		}else{
			$imgUrl = AI_DRAW_HOST.$result;
			$db = \hsTool\db::getInstance('aidraw');
			$addData = array(
				'drawUrl'      => $imgUrl,
				'userRandom'   => $_POST['random'],
				'createtime'   => time()
			);
			$drawId = $db->add($addData);
			if(!$drawId){exit(jsonCode('error4', '服务器忙请重试'));}
		}
		exit(jsonCode('ok', $imgUrl));
	}
}