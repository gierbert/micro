<?php
namespace hsC;
class category{
	
	public function index(){
		$_GET['pid'] = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
		$db = \hsTool\db::getInstance('categories');
		if(empty($_GET['pid'])){
			$categories = $db->fetchAll();
		}else{
			$categories = $db->where('pid = ?', array($_GET['pid']))->fetchAll();
		}
		if(empty($categories)){exit(jsonCode('empty', ''));}
		$caties = array();
		foreach($categories as $cate){
			$caties[$cate['id']] = $cate['name']; 
		}
		exit(jsonCode('ok', $caties));
	}
	
}