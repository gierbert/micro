<?php

//设置编码
header('content-type:text/html; charset=utf-8');
	
// 接口认证
if(empty($_GET['token'])){exit(jsonCode('error', 'token error'));}
if($_GET['token'] != 'api2022'){exit(jsonCode('error', 'token error'));}

// 文件夹定义
define("HS_DS"            , DIRECTORY_SEPARATOR);
define("HS_ROOT"          , dirname(__FILE__).HS_DS);
define("HS_CONTROLLERS"   , HS_ROOT.'controllers'.HS_DS);
define("HS_MODELS"        , HS_ROOT.'models'.HS_DS);
define("HS_TOOLS"         , HS_ROOT.'tools'.HS_DS);

/* 过滤及定义 POST */
if(!empty($_POST)){
	define("IS_POST", false);
}else{
	define("IS_POST", true);
	$_POST = str_replace(array('<','>', '"', "'"),array('&lt;','&gt;', '&quot;', ''), $_POST);
}

/* 数据库配置 */
define('HS_DB_HOST'    , '127.0.0.1'); // mysql 服务器地址
define('HS_DB_NAME'    , 'micro');     // 数据库名称
define('HS_DB_USER'    , 'root');      // 数据库账号
define('HS_DB_PWD'     , 'root');      // 数据库密码
define('HS_DB_PRE'     , 'micro_');    // 数据表统一前缀
define('HS_DB_CHARSET' , 'utf8mb4');   // mysql 字符集类型

/* 微信小程序相关设置  */
define('HS_APPID'  , 'wx6972b8568751d87b');
define('HS_SECRET' , 'd17507b5a3c025453af38f46f104046b');

/* AI服务器配置 */
define('AI_DRAW_HOST' , 'http://127.0.0.1:8081/WeixinProject/micro-start/'); //图片保存地址
define('AI_DRAW_TXT2IMG' , 'http://127.0.0.1:7860/sdapi/v1/txt2img'); //文字转图片
define('AI_DRAW_OPTIONS' , 'http://127.0.0.1:7860/sdapi/v1/options'); //模型配置
/* 自动加载 */
function hsAutoLoad($className){
	$className = explode('\\', $className);
	if(empty($className[0])){array_shift($className);}
	if(count($className) != 2){return false;}
	switch($className[0]){
		case 'hsModel':
			$classFileName = HS_MODELS.$className[1].'.php';
		break;
		case 'hsTool':
			$classFileName = HS_TOOLS.$className[1].'.php';
		break;
	}
	if(empty($classFileName)){return false;}
	if(is_file($classFileName)){require $classFileName;}
}
spl_autoload_register("hsAutoLoad");


/* 路由解析 */
$_GET['c'] = empty($_GET['c']) ? 'index' : $_GET['c'];
$_GET['m'] = empty($_GET['m']) ? 'index' : $_GET['m'];
$pattern = '/^[a-zA-Z]+[0-9]*[a-zA-Z]*$/';
if(!preg_match($pattern, $_GET['c'])){$_GET['c'] = 'index';}
if(!preg_match($pattern, $_GET['m'])){$_GET['m'] = 'index';}
$controllerFileName = HS_CONTROLLERS.$_GET['c'].'.php';
if(is_file($controllerFileName)){
	require $controllerFileName;
	$className = '\\hsC\\'.$_GET['c'];
	$controller = new $className;
	if(method_exists($controller, $_GET['m'])){
		call_user_func(array($controller, $_GET['m']));
	}
}

// json 输出
function jsonCode($status, $data){
	return json_encode(array('status' => $status, 'data' => $data));
}

// 签名验证
function checkSign(){
	if(empty($_POST['sign'])){exit(jsonCode('error', 'sign error'));}
	$sign = explode('-', $_POST['sign']);
	if(count($sign) != 2){exit(jsonCode('error', 'sign error'));}
	$db = \hsTool\db::getInstance('access_tokens');
	$token = $db->where('token = ?', array($sign[1]))->fetch();
	if(empty($token)){exit(jsonCode('error', 'sign error'));}
	$signMd5 = md5($token['token'].$token['time']);
	if($signMd5 != $sign[0]){exit(jsonCode('error', 'sign error'));}
	// 验证成功则删除
	$db->where('token = ?', array($sign[1]))->delete();
}

// 验证用户合法性
function checkUser(){
	if(empty($_POST['uid'])){exit(jsonCode('error', 'uid error'));}
	if(empty($_POST['random'])){exit(jsonCode('error', 'random error'));}
	$db   = \hsTool\db::getInstance('users');
	$user = $db->where('id = ?', array($_POST['uid']))->fetch();
	$newMesNum = $db
				->join('as a left join micro_message as b on a.random = b.random_r')
				->where('b.random_r = ? AND b.isread = 0', array($_POST['random']))
				->fetchAll();
	$newMesNum = array("newMesNum" => count($newMesNum));
	$user = array_merge($user,$newMesNum);
	if(empty($user)){exit(jsonCode('error', 'user error'));}
	return $user;
}
function getUser(){
	if(empty($_POST['random'])){exit(jsonCode('error', 'random error'));}
	$db   = \hsTool\db::getInstance('users');
	$user = $db->where('random = ?', array($_POST['random']))->fetch();
	if(empty($user)){exit(jsonCode('error', 'user error'));}
	return $user;
}

function assoc_unique($arr, $key) {
	$tmp_arr = array();
	foreach ($arr as $k => $v) {
		if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
			unset($arr[$k]);
		} else {
			$tmp_arr[] = $v[$key];
			}
	}
 sort($arr); //sort函数对数组进行排序
 return $arr;
}
//每隔固定长度插入指定字符
function chunkSplit($string, $length, $end){
	$array = array();
	$strlen = mb_strlen($string);
	while($strlen){
		$array[] = mb_substr($string, 0, $length, "utf-8");
		$string = mb_substr($string, $length, $strlen, "utf-8");
		$strlen = mb_strlen($string);
	}
	return implode($end, $array);
}

/**
 * 发送post请求
 * @param string $url 请求地址
 * @param array $post_data post键值对数据
 * @return string
 */
function send_post($url, $post_data) { 
  $postdata = json_encode($post_data);
  set_time_limit(0);
  $options = array(
     'http' => array(
	 'method' => 'POST',
     'header' => "Content-Type:application/json\r\n".
				 "Authorization:Basic c3RhYmxlRGlmZnVzaW9uOnNkMTIz",
     'content' => $postdata,
     'timeout' => 1000// 超时时间（单位:s）
    )
  );
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  return $result;
}  

function send_get($url, $post_data) { 
  $postdata = json_encode($post_data);
  set_time_limit(0);
  $options = array(
     'http' => array(
	 'method' => 'GET',
     'header' => "Content-Type:application/json\r\n".
				 "Authorization:Basic c3RhYmxlRGlmZnVzaW9uOnNkMTIz",
     'content' => $postdata,
     'timeout' => 1000// 超时时间（单位:s）
    )
  );
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  return $result;
} 


//base64转图片

function base64_image_content($base64_image_content){
				//图片保存路径
				$new_file = "static/images/".date('Ymd',time()).'/';
				if (!file_exists($new_file)) {
					mkdir($new_file,0755,true);
				}
				//图片名字
				$new_file = $new_file.time().'.'.'png';
				if (file_put_contents($new_file,base64_decode($base64_image_content))) {
					return $new_file;
				} else {
					return 'error';
				}
		}
		
?>