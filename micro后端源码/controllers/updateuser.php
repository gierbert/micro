<?php
namespace hsC;
class updateuser{
	public function index(){
		checkSign();
		if(empty($_POST['userid'])){exit(jsonCode('error', 'user id error ...'));}
		$db = \hsTool\db::getInstance('users');
		$data = array(
			'username'     => $_POST['name'],
			'face'     => $_POST['face'],
			'school'     => $_POST['school'],
			'college'     => $_POST['college'],
			'gender'     => $_POST['gender'],
			'signature'     => $_POST['signature']
		);
		$db->where('id = ?', array($_POST['userid']))->update($data);
		exit(jsonCode('ok', 'ok'));
	}
}