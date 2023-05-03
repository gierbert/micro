<?php
namespace hsC;
class my{
	public function removeArt(){
		$user = checkUser();
        if(empty($_POST['artId'])){exit(jsonCode('error', 'art id error ...'));}
        // 查询文章
        $db = \hsTool\db::getInstance('posts');
        $art = $db->where('id = ?', array($_POST['artId']))->fetch();
        if(empty($art)){exit(jsonCode('error', 'art id error ...'));}
        // 不是自己的文章不能删除
        if($art['uid'] != $user['id']){exit(jsonCode('error', 'user error'));}
        // 删除
        $db->where('id = ?', array($_POST['artId']))->delete();
        // 扣除积分
        $memberDb = \hsTool\db::getInstance('users');
        $memberDb->where('id = ?', array($user['id']))->filed('experience', -10);
        exit(jsonCode('ok', 'ok'));
	}
	
	public function getReplyList(){
		
		if(empty($_GET['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('message');
		$send = $db
				->join('as a left join micro_users as b on a.random_r = b.random')
				->order('a.id desc')
				->where('a.random_s = ?', array($_GET['random']))
				->fetchAll('a.createtime,a.message,b.face,b.username,b.random');
		$receive = $db
				->join('as a left join micro_users as b on a.random_s = b.random')
				->order('a.id desc')
				->where('a.random_r = ?', array($_GET['random']))
				->fetchAll('a.createtime,a.message,b.face,b.username,b.random');
		//获取未读消息数量
		$newMesNum = $db
					->where('random_r = ? AND isread = 0', array($_GET['random']))
					->fetchAll('random_s');
		$data = array();
		foreach($newMesNum as $key => $value){
			$data[] = $newMesNum[$key]['random_s'];
		}
		
		$newMesNum = array_count_values($data);
		//获取消息用户
		$reply = array_merge($send,$receive);
		$key = 'random';
		$reply = assoc_unique($reply,$key);
		rsort($reply);
		foreach($reply as $k => $v){
			$reply[$k]['createtime'] = date('Y-m-d H:i', $reply[$k]['createtime']);
			foreach($newMesNum as $i => $j){
				if($reply[$k]['random'] == $i){
					$reply[$k]['newMesNum'] = $j;
				}
			}
		}
		
		if(empty($reply)){exit(jsonCode('empty', ''));}
		exit(jsonCode('ok', $reply));
	}
	
	public function getFollowList(){
		if(empty($_GET['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('users');
		$follow = $db->where('random = ?', array($_GET['random']))->fetch('follow');
		$arr = json_decode($follow['follow']);
		$users = array();
		foreach($arr as $key => $value){
			$user = $db
					->where('random = ?', array($arr[$key]))
					->order('id desc')
					->fetchAll('username, face, random');
			$users = array_merge($users,$user);
		}
		if(empty($users)){exit(jsonCode('empty', ''));}
		exit(jsonCode('ok', $users));
	}
	
	public function getFansList(){
		if(empty($_GET['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('users');
		$fans = $db->where('random = ?', array($_GET['random']))->fetch('fans');
		$arr = json_decode($fans['fans']);
		$users = array();
		foreach($arr as $key => $value){
			$user = $db
					->where('random = ?', array($arr[$key]))
					->order('id desc')
					->fetchAll('username, face, random');
			$users = array_merge($users,$user);
		}
		if(empty($users)){exit(jsonCode('empty', ''));}
		exit(jsonCode('ok', $users));
	}
	
	public function postfollow(){
		if(empty($_POST['post_follow'])){exit(jsonCode('error', 'post_follow error ...'));}
		if(empty($_POST['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('users');
		$data = array(
			'post_follow'      => $_POST['post_follow']
		);
		$db->where('random = ?', array($_POST['random']))->update($data);
		exit(jsonCode('ok', 'ok'));
	}
	
	public function depostfollow(){
		if(empty($_POST['postId'])){exit(jsonCode('error', 'postId error ...'));}
		if(empty($_POST['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('users');
		$post_follow = $db->where('random = ?', array($_POST['random']))->fetch('post_follow');
		$arr = json_decode($post_follow['post_follow']);
		foreach($arr as $key => $value){
			if($arr[$key] == $_POST['postId']){
				unset($arr[$key]);
			}
		}
		$arr = array_values($arr);
		$data = array(
			'post_follow'		=> json_encode($arr)
		);
		$db->where('random = ?', array($_POST['random']))->update($data);
		exit(jsonCode('ok', 'ok'));
	}
	
	public function follow(){
		if(empty($_POST['random'])){exit(jsonCode('error', 'random error ...'));}
		$db = \hsTool\db::getInstance('users');
		$data1 = array(
			'follow'      => $_POST['follow']
		);
		$db->where('random = ?', array($_POST['random']))->update($data1);
		if($_POST['is'] == 1){$db->where('random = ?', array($_POST['random']))->filed('follow_num', 1);}
		if($_POST['is'] == 0){$db->where('random = ?', array($_POST['random']))->filed('follow_num', -1);}
		
		$data2 = array(
			'fans'      => $_POST['fans']
		);
		$db->where('random = ?', array($_POST['follow_random']))->update($data2);
		if($_POST['is'] == 1){$db->where('random = ?', array($_POST['follow_random']))->filed('fans_num', 1);}
		if($_POST['is'] == 0){$db->where('random = ?', array($_POST['follow_random']))->filed('fans_num', -1);}
		
		$user = $db->where('random = ?', array($_POST['follow_random']))->fetch();
		exit(jsonCode('ok', $user));
	}
	
	public function info(){
		$user = checkUser();
		// 查询会员文章总数
		$dbArt = \hsTool\db::getInstance('posts');
		$artCountNumber = $dbArt->where('uid = ?', array($user['id']))->count();
		$user['postCount'] = $artCountNumber;
		exit(jsonCode('ok', $user));
	}
	
	public function getUserInfo(){
		$user = getUser();
		// 查询会员文章总数
		$dbArt = \hsTool\db::getInstance('posts');
		$artCountNumber = $dbArt->where('uid = ?', array($user['id']))->count();
		$data = array(
			'postCount'		=> $artCountNumber
		);
		$db = \hsTool\db::getInstance('users');
		$db->where('id = ?', array($user['id']))->update($data);
		$user['postCount'] = $artCountNumber;
		exit(jsonCode('ok', $user));
	}
	
	public function arts(){
		$user = checkUser();
		$db = \hsTool\db::getInstance('posts');
		$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$arts = $db
				->where('art_uid = ?', array($user['u_id']))
				->order('art_id desc')
				->limit(($page - 1) * 10, 10)
				->fetchAll();
		if(empty($arts)){exit(jsonCode('empty', ''));}
		exit(jsonCode('ok', $arts));
	}
	
}