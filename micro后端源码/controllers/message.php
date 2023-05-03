<?php
namespace hsC;
class message{
	
	public function add(){
		if(empty($_POST['random_s'])){exit(jsonCode('error1', 'random_s error ...'));}
		$dbMessage = \hsTool\db::getInstance('message');
		/* $Message = chunkSplit($_POST['message'],15,"\n"); */
		$addData = array(
			'random_s'      => $_POST['random_s'],
			'random_r'        => $_POST['random_r'],
			'message'    => $_POST['message'],
			'createtime' => time()
		);
		$messageId = $dbMessage->add($addData);
		if(!$messageId){exit(jsonCode('error', '服务器忙请重试'));}
		exit(jsonCode('ok', 'ok'));
	}
	
	public function addimg(){
		if(empty($_POST['random_s'])){exit(jsonCode('error1', 'random_s error ...'));}
		$dbMessage = \hsTool\db::getInstance('message');
		$addData = array(
			'random_s'      => $_POST['random_s'],
			'random_r'      => $_POST['random_r'],
			'img'    		=> $_POST['img'],
			'createtime' => time()
		);
		$messageId = $dbMessage->add($addData);
		if(!$messageId){exit(jsonCode('error', '服务器忙请重试'));}
		exit(jsonCode('ok', 'ok'));
	}
	
	public function getMessageList(){
		if(empty($_GET['random'])){exit(jsonCode('error', 'random error ...'));}
		if(empty($_GET['follow_random'])){exit(jsonCode('error', 'follow_random error ...'));}
		$_GET['page'] = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$db = \hsTool\db::getInstance('message');
		//获取双方发送的消息
		$left = $db
				->join('as a left join micro_users as b on a.random_s = b.random')
				->order('a.id desc')
				->where('a.random_r = ? AND a.random_s = ?', array($_GET['random'],$_GET['follow_random']))
				->fetchAll('a.*,b.face,b.username');
		$right = $db
				->join('as a left join micro_users as b on a.random_s = b.random')
				->order('a.id desc')
				->where('a.random_s = ? AND a.random_r = ?', array($_GET['random'],$_GET['follow_random']))
				->fetchAll('a.*,b.face,b.username');
		$messageList = array_merge($left,$right);
		rsort($messageList);
		$messageList = array_slice($messageList,($_GET['page'] - 1) * 10, 10);
		sort($messageList);
		//将消息的已读信息置 1
		$data = array('isread' => 1);
		$db->where('random_r = ?', array($_GET['random']))->update($data);
		//返回信息
		if(empty($messageList)){exit(jsonCode('empty', ''));}
		foreach($messageList as $key => $value){
			$messageList[$key]['createtime'] = date('Y-m-d H:i', $messageList[$key]['createtime']);
		}
		exit(jsonCode('ok', $messageList));
	}
	
	
}