<?php
namespace hsC;
class posts{
	
	public function infoWithUser(){
		if(empty($_GET['artid'])){exit(jsonCode('error', 'art data error'));}
        $_GET['artid'] = intval($_GET['artid']);
        $dbArticles = \hsTool\db::getInstance('posts');
        $art = $dbArticles
                ->join('as a left join micro_users as b on a.uid = b.id')
                ->where('a.id = ?', $_GET['artid'])
                ->fetch('a.*, b.username, b.face, b.random');
        if(empty($art)){exit(jsonCode('empty', ''));}
        $art['createtime'] = date('Y-m-d H:i', $art['createtime']);
        exit(jsonCode('ok', $art));
	}
	
	public function getHomePost(){
		$_GET['Type'] = empty($_GET['Type']) ? 1 : intval($_GET['Type']);
	    $_GET['page'] = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$user = \hsTool\db::getInstance('users');
		
		if($_GET['Type'] == 1){
		    $follow = $user->where('random = ?', array($_GET['Random']))->fetch('follow');
		    $posts = array();
		    $arr = json_decode($follow['follow']);
		    foreach($arr as $key => $value){
		    	$post = $user
		    			->join('as a left join micro_posts as b on a.id = b.uid')
		    			->where('a.random = ?', array($arr[$key]))
		    			->order('b.id desc')
		    			->fetchAll('b.*, a.username, a.face, a.random');
		    	$posts = array_merge($posts,$post);
		    }
		    rsort($posts);
		    $posts = array_slice($posts,($_GET['page'] - 1) * 10, 10);
		}

		if($_GET['Type'] == 2){
			$dbpost = \hsTool\db::getInstance('posts');
			$posts = $dbpost
					->join('as a left join micro_users as b on a.uid = b.id')
					->order('a.praise desc')
					->limit(($_GET['page'] - 1) * 10, 10)
					->fetchAll('a.*, b.username, b.face, b.random');
		}
		
		if($_GET['Type'] == 3){
			$post_follow = $user->where('random = ?', array($_GET['Random']))->fetch('post_follow');
			$posts = array();
			$arr = json_decode($post_follow['post_follow']);
			foreach($arr as $key => $value){
				$post = $user
						->join('as a left join micro_posts as b on a.id = b.uid')
						->where('b.id= ?', array($arr[$key]))
						->order('b.id desc')
						->fetchAll('b.*, a.username, a.face, a.random');
				$posts = array_merge($posts,$post);
			}
			$posts = array_slice($posts,($_GET['page'] - 1) * 10, 10);
		}
	    if(empty($posts)){exit(jsonCode('empty', ''));}
		foreach($posts as $key => $value){
			$posts[$key]['createtime'] = date('Y-m-d H:i', $posts[$key]['createtime']);
		}
	    exit(jsonCode('ok', $posts));
	}
	
	public function getMyList(){
		$_GET['page'] = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$dbArticles = \hsTool\db::getInstance('posts');
		$arts = $dbArticles
			->join('as a left join micro_users as b on a.uid = b.id')
			->where('b.random = ?', array($_GET['random']))
			->order('a.id desc')
			->limit(($_GET['page'] - 1) * 10, 10)
			->fetchAll('a.*, b.username, b.face, b.random');
		if(empty($arts)){exit(jsonCode('empty', ''));}
		foreach($arts as $key => $value){
			$arts[$key]['createtime'] = date('Y-m-d H:i', $arts[$key]['createtime']);
		}
		exit(jsonCode('ok', $arts));
	}
	
	public function getList(){
		$_GET['cate'] = empty($_GET['cate']) ? 0 : intval($_GET['cate']);
        $_GET['page'] = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$_GET['keywords'] = empty($_GET['keywords']) ? 0 : intval($_GET['keywords']);
		$keywords = $_GET['keywords'];
        $dbArticles = \hsTool\db::getInstance('posts');
        if(empty($keywords)){
			if(empty($_GET['cate'])){
			    $arts = $dbArticles
					->join('as a left join micro_users as b on a.uid = b.id')
			    	->order('a.id desc')
					->limit(($_GET['page'] - 1) * 10, 10)
			    	->fetchAll('a.*, b.username, b.face, b.random');
			}else{
			    $arts = $dbArticles
					->join('as a left join micro_users as b on a.uid = b.id')
			    	->where('a.cate = ?', array($_GET['cate']))
			    	->order('a.id desc')
					->limit(($_GET['page'] - 1) * 10, 10)
					->fetchAll('a.*, b.username, b.face, b.random');
			}
		}
		else{
			if(empty($_GET['cate'])){
			    $arts = $dbArticles
					->join('as a left join micro_users as b on a.uid = b.id')
					->where('a.title LIKE ?',array('%'.$keywords.'%'))
			    	->order('a.id desc')
					->limit(($_GET['page'] - 1) * 10, 10)
			    	->fetchAll('a.*, b.username, b.face, b.random');
			}else{
				$arts = $dbArticles
					->join('as a left join micro_users as b on a.uid = b.id')
					->where('a.title LIKE ? AND a.cate = ?',array('%'.$keywords.'%',$_GET['cate']))
					->order('a.id desc')
					->limit(($_GET['page'] - 1) * 10, 10)
					->fetchAll('a.*, b.username, b.face, b.random');
			}
		}
        if(empty($arts)){exit(jsonCode('empty', ''));}
		foreach($arts as $key => $value){
			$arts[$key]['createtime'] = date('Y-m-d H:i', $arts[$key]['createtime']);
		}
        exit(jsonCode('ok', $arts));
	}
	
	public function deletePost(){
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
	    exit(jsonCode('ok', 'ok'));
	}
	public function getPraise(){
		if(empty($_POST['artid'])){exit(jsonCode('error', 'art data error'));}
		$dbArticles = \hsTool\db::getInstance('posts');
		$praise_uid = $_POST['praise_uid'];
		$data = array(
			'praise'     => $_POST['praise'],
			'praise_uid' => $praise_uid
		);
		$dbArticles->where('id = ?', $_POST['artid'])->update($data);
		$memberDb = \hsTool\db::getInstance('users');
		$memberDb->where('id = ?', array($_POST['uid']))->filed('experience', 5);
		exit(jsonCode('ok', 'ok'));
	}
	public function edit(){
		// 验证签名
		checkSign();
		// 验证用户合法性
		$user = checkUser();
		// 检查文章
		if(empty($_GET['artid'])){exit(jsonCode('error', 'art data error'));}
		$dbArticles = \hsTool\db::getInstance('posts');
		$art = $dbArticles->where('id = ?', $_GET['artid'])->fetch();
		if(empty($art)){exit(jsonCode('error', 'art data error'));}
		if($art['uid'] != $user['id']){exit(jsonCode('error', 'art data error'));}
		$data = array(
			'title'      => $_POST['title'],
			'uid'        => $user['id'],
			'cate'       => intval($_POST['cate']),
			'content'    => $_POST['content']
		);
		$dbArticles->where('id = ?', $_GET['artid'])->update($data);
		exit(jsonCode('ok', 'ok'));
	}
	
	public function info(){
		if(empty($_GET['artid'])){exit(jsonCode('error', 'art data error'));}
		$_GET['artid'] = intval($_GET['artid']);
		$dbArticles = \hsTool\db::getInstance('posts');
		$art = $dbArticles->where('id = ?', $_GET['artid'])->fetch();
		if(empty($art)){exit(jsonCode('empty', ''));}
		exit(jsonCode('ok', $art));
	}
	
	public function add(){

		/* $_POST 格式 
		(
		    [title] => title
		    [content] => [
		 * 		{"type":"image","content":"http://192.168.31.188/imgs/5c174b0fb3825.png"},
		 * 		{"type":"text","content":"hi123"},
		 * 		{"type":"image","content":"http://192.168.31.188/imgs/5c174b0fc3297.png"},
		 * 		{"type":"text","content":"hi222"}]
		    [uid] => 8
		    [random] => ****
			[cate] => 1
		    [sign] => sign
		)
		*/
		// 验证签名
		checkSign();
		// 验证用户合法性
		$user = checkUser();
		// 提交主要信息
		$dbArticles = \hsTool\db::getInstance('posts');
		$addData = array(
			'title'      => $_POST['title'],
			'uid'        => $user['id'],
			'cate'       => intval($_POST['cate']),
			'content'    => $_POST['content'],
			'createtime' => time()
		);
		$articleId = $dbArticles->add($addData);
		if(!$articleId){exit(jsonCode('error', '服务器忙请重试'));}
		// 更新等级经验
		$memberDb = \hsTool\db::getInstance('users');
		$memberDb->where('id = ?', array($user['id']))->filed('experience', 10);
		exit(jsonCode('ok', 'ok'));
	}
}
