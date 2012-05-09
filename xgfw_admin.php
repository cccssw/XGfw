<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/xgfw_func.php');
$xgfw_ban = require(dirname(__FILE__).'/black_list_sorted.php');
$xgfw_pass = require(dirname(__FILE__).'/white_list_sorted.php');
$appFile = basename(__FILE__);
	@session_start();
    $pass = 'wwssccc';
    $user = 'cccssw';
    
   
    function check_login(){
            if(!isset($_SESSION['xgfw_admin'])){
                    echo "<script>window.location.href='".$appFile."?action=index'</script>";
                    exit;
            }               
    }
    if(defined('SAE_TMP_PATH')) {
    	$kv = new SaeKV();       
    	$ret = $kv->init();
    }
    $action = isset($_GET['action'])?$_GET['action']:'index';
    $key = isset($_REQUEST['key'])?$_REQUEST['key']:'';
    $value = isset($_POST['value'])?$_POST['value']:'';       

?>
<title>xGfw Admin - By chinaalex.com</title>
<div id="header">
        <h2>XGfw Manager</h2>
        <a href="<?php echo $appFile;?>?action=default">Index</a> | 
        <a href="<?php echo $appFile;?>?action=set">SET</a> | 
        <a href="<?php echo $appFile;?>?action=get">GET</a>  | 
        <a href="<?php echo $appFile;?>?action=del">DEL</a>  | 
        <a href="<?php echo $appFile;?>?action=allkey">ALL KV</a>   | 
        <a href="<?php echo $appFile;?>?action=dump_kvdb_ban">Dump Kvdb Ban</a>|   | 
        <a href="<?php echo $appFile;?>?action=logout">Logout</a></br>
        <a href="<?php echo $appFile;?>?action=sort_black" target="_blank">Sort_black_list</a>|
        <a href="<?php echo $appFile;?>?action=sort_white" target="_blank">Sort_white_list</a>|
        <a href="<?php echo $appFile;?>?action=testIP">Test Ip</a>|
         <a href="<?php echo $appFile;?>?action=allban" >All Banned</a>
</div>
<?php
	if($action == 'index'){
    	echo '<form action="?action=login" method="post">UserName:'."<input type='text' name='user' value='' /><br/>Password:<input type='password' name='passwd' value='' /><br/><input type='submit' name='submit' value='登录' /><br/></form>";
    }else if($action == 'login'){
    	$p_pass = isset($_POST['passwd'])?$_POST['passwd']:'';
    	$p_user = isset($_POST['user'])?$_POST['user']:'';
        if($p_pass == $pass && $p_user == $user ){
                        $_SESSION['xgfw_admin'] = 1;
        }
        echo "<script>window.location.href='".$appFile."?action=default'</script>";
    }else if($action =='logout'){
    	@session_destroy();
        echo "<script>window.location.href='".$appFile."?action=index'</script>";
    }else if($action == 'default'){
    	check_login();
          echo "welcome the state of the xgfw firewall:"."</br>";
    	echo "black_list:".count($xgfw_ban)."</br>";
    	echo "white_list:".count($xgfw_pass)."</br>";
        echo "Ban by url definition:".BAN_BY_URL." rules(preg_match):[";
        foreach($GLOBALS["xgfw_ban_urls"] as $key=>$val){
        	echo $val;
        }
        echo "]</br>";
        echo "Ban by refer definition:".BAN_BY_REFER." rules(preg_match):[";
        foreach($GLOBALS["xgfw_ban_refers"] as $key=>$val){
        	echo $val;
        }
        echo "]</br>";
        echo "Ban by User-Agent definition:".BAN_BY_UA." rules(preg_match):[";
        foreach($GLOBALS["xgfw_ban_uas"] as $key=>$val){
        	echo $val;
        }
        echo "]</br>";
    }else if($action == 'sort_black'){
    	check_login();
    	xGfw_quick_sort($xgfw_ban);
    	//var_dump($xgfw_ban);
    	
    	echo "Total:".count($xgfw_ban)."</br>";
    	foreach($xgfw_ban as $key => $val){
    		echo "'".$val."',"."</br>";
    	}
    	/*
    	if(binary_search_sorted($xgfw_ban,'116.228.153.242')){
    		echo "true";
    	}else{echo "false";}
    	*/
    }else if($action == 'sort_white'){
    	check_login();
    	check_login();
    	xGfw_quick_sort($xgfw_pass);
    	//var_dump($xgfw_ban);
    	
    	echo "Total:".count($xgfw_pass)."</br>";
    	foreach($xgfw_pass as $key => $val){
    		echo "'".$val."',"."</br>";
    	}
    	/*
    	if(binary_search_sorted($xgfw_ban,'116.228.153.242')){
    		echo "true";
    	}else{echo "false";}
    	*/
    }else if ($action =='allkey'){
	    check_login();
	    $ret = $kv->pkrget('', 100);    
	    while (true) {                   
	            foreach($ret as $k=>$v)
	                    echo "<p>K：{$k}      <a href=\"{$appFile}?action=get&key={$k}\">GET</a> | <a href=\"{$appFile}?action=del&key={$k}\" onclick=\"return confirm('确认删除？');\" style='color:red;'>DEL</a></p>";
	            end($ret);                               
	            $start_key = key($ret);
	            $i = count($ret);
	            if ($i < 100) break;
	            $ret = $kv->pkrget('abc', 100, $start_key);
		}
	}else if ($action =='allban'){
	    check_login();
	    $ret = $kv->pkrget('ban', 100);  
            foreach($ret as $k=>$v)
	                    echo "<p>K：{$k}      <a href=\"{$appFile}?action=get&key={$k}\">GET</a> | <a href=\"{$appFile}?action=del&key={$k}\" onclick=\"return confirm('确认删除？');\" style='color:red;'>DEL</a></p>";
          //end($ret);       
          /*
            while (true) {                   
	            foreach($ret as $k=>$v)
	                    echo "<p>K：{$k}      <a href=\"{$appFile}?action=get&key={$k}\">GET</a> | <a href=\"{$appFile}?action=del&key={$k}\" onclick=\"return confirm('确认删除？');\" style='color:red;'>DEL</a></p>";
	            end($ret);                               
	            $start_key = key($ret);
	            $i = count($ret);
	            if ($i < 100) break;
	            $ret = $kv->pkrget('abc', 100, $start_key);
}*/
	}else if($action == 'del'){
        check_login();
        if(!empty($key) ){
                $v = $kv->delete($k);
                echo "<p>K:{$k}删除成功！</p>";
               
        }else if(!empty($_GET['key'])){
                $v = $kv->delete($_GET['k']);
                echo "<p>K:{$_GET['key']}删除成功！</p>";
               
        }
        else{
        	$str=<<<EOF
 <form action="{$appFile}?action=del" name="setform" method="post">
                        <p>  Key:<input type="text" name="key" value="" /></p>
                        <p>    <input type="submit"  value="删除" /></p>
 </form>
EOF;
			echo $str;
        }
	}else if ($action == 'get'){
        check_login();
        if(!empty($key)){
                if(@file_exists('saekv://'.$key)){
                        echo "<p>取值成功:{$key} => <pre>";
                        echo htmlspecialchars(file_get_contents('saekv://'.$key));
                        echo "</pre></p>";
                }else{
                        $v = $kv->get($key);
                        if($v !== false){
                                echo "<p>取值成功:{$key} => <pre>";
                                if(is_array($v)){                                       
                                        print_r($v);
                                }else if(is_string($v)){
                                        echo $v;
                                }else{
                                        var_dump($v);
                                }
                                echo "</pre></p>";
                        }else{
                                echo "<p>{$key}不存在！</p>";
                        }
                }
               
        }else{
        	$str=<<<EOF
<form action="{$appFile}?action=get" name="setform" method="post">
                        <p>  Key:<input type="text" name="key" value="" /></p>
                        <p>    <input type="submit"  value="Get" /></p>
</form>
EOF;
			echo $str;
        }
    }else if($action == 'set'){
        check_login();
        if(!empty($key) && !empty($value) ){
        	$kv->set($key,$value);
        	echo "<p>设置成功:{$key} => {$value}</p>";
        }else{
?>				
<form action="<?php echo $appFile;?>?action=set" name="setform" method="post">
    <p>  Key:<input type="text" name="key" value="" /></p>
    <p>Value:<input type="text" name="value" value="" /></p>
    <p>    <input type="submit"  value="设置" /></p>
</form>
<?php

        }
    }else if($action == 'testIP'){
    	$str=<<<EOF
<form action="xgfw_test.php" name="form" method="get"  target="_blank">
      <p>  IP:<input type="text" name="ip" value="" /></p>
       <p><input type="submit"  value="Test" /></p>
</form>
EOF;
	echo $str; 
    }else if($action == 'dump_kvdb_ban'){
        $ret = $kv->pkrget('ban_', 100);
  	foreach($ret as $key => $value){
  		$res=$key.'\n';
                file_put_contents('saemc://ban_ip.txt',$res,FILE_APPEND);
                $kv->delete($key);
        }
  	$res = file_get_contents('saemc://ban_ip.txt');
        echo $res;
    }
			
