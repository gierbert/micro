<?php
namespace hsC;
class comment{
	public function getComment(){
		$dbGetcomment = \hsTool\db::getInstance('comment');
		$comment = $dbGetcomment 
					-> where('com_postid = ?', array($_GET['artid']))
					-> join('as a left join micro_users as b on a.com_uid = b.id')
					-> order('a.id asc')
					-> fetchAll('a.*, b.username, b.face, b.random');
		if(empty($comment)){exit(jsonCode('empty', ''));}
		foreach($comment as $key => $value){
			$comment[$key]['com_createtime'] = date('Y-m-d H:i', $comment[$key]['com_createtime']);
		}
		exit(jsonCode('ok', $comment));
	}
	public function putComment(){
		$dbPutcomment = \hsTool\db::getInstance('comment');
		$comment = array(
			'com_uid'        => $_POST['userId'],
			'com_postid'     => $_POST['artid'],
			'com_content'    => $_POST['content'],
			'com_createtime' => time()
		);
		$commentId = $dbPutcomment -> add($comment);
		if(!$commentId){exit(jsonCode('error', '服务器忙请重试'));}
		$memberDb = \hsTool\db::getInstance('users');
		$memberDb->where('id = ?', array($_POST['userId']))->filed('experience', 10);
		exit(jsonCode('ok', 'ok'));
	}
	public function deleteComment(){
		$user = checkUser();
		if(empty($_POST['com_id'])){exit(jsonCode('error', 'com_id error ...'));}
		$dbdeletecomment = \hsTool\db::getInstance('comment');
		$deletecomment = $dbdeletecomment->where('id = ?', array($_POST['com_id']))->fetch();
		if(empty($deletecomment)){exit(jsonCode('error', 'deletecomment error ...'));}
		if($deletecomment['com_uid'] != $user['id']){exit(jsonCode('error', 'user error'));}
		// 删除
		$dbdeletecomment->where('id = ?', array($_POST['com_id']))->delete();
		exit(jsonCode('ok', 'ok'));
	}
}