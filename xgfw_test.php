<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/xgfw_func.php');
$xgfw_ban = require(dirname(__FILE__).'/black_list_sorted.php');
$xgfw_pass = require(dirname(__FILE__).'/white_list_sorted.php');
define('XGFW_TEST',1);
@session_start();
if($_GET['destory']==1){
	sessionDestroy();
}
if($_GET['action']=='emptyCnt'){
	$c = new SaeCounter ();
        $cnt = $c->getall();
  	foreach($cnt as $key=>$val){
  		$c->remove($key);
        }
        echo "counter empty!";
}
$white =false;
$ip = xGfw_GetIP();
if(isset($_GET['ip']))
	$ip = $_GET['ip'];
if(isset($_GET['url']))
	$url = $_GET['url'];
if(isset($_GET['refer']))
	$refer = $_GET['refer'];
if(isset($_GET['ua']))
	$refer = $_GET['ua'];


echo "<h3 style='color:lightblue;margin-bottom:0;'>ip:".$ip."</h3></br>";
xGfw_MmcWithCounter($ip);
$white = xGfw_HardCode_Ban($ip,$xgfw_ban,$xgfw_pass);
if($white){
  	echo "<p style='color:green'>is_in_white_list:".$white."</p></br>";
}else{
	echo "<p style='color:orange'>is_in_white_list:".$white."</p></br>";
}

xGfw_BANB_Http_Defined($url,$refer,$ua);
echo "kvdb ..here"."</br>";
xGfw_Kvdb($ip,1);//ban by kvdb action
        

echo "<h2 style='color:green'>No red text means passed</h2>";


//ban functions
function xGfw_HardCode_Ban($ip="127.0.0.1",$ban,$pass){
	$ips = explode( ',' ,$ip );
	if(defined('XGFW_TEST')){
          echo "<p style='color:lightblue;margin-bottom:0;'>ips:";
		print_r($ips);
          	echo "</p></br>";
	}
	if( !xGfw_binary_search_sorted($pass,$ips[0]) ){
      	  if(xGfw_binary_search_sorted($ban,$ips[0])){
          	$_SESSION[SESSION_BAN_ID] = 1;
          	xGfw_ShowNotice ( 'ddos_reflect','in PHP coded black_list' );
          	exit;
          }
          
          return 0;
	}
        $_SESSION[SESSION_PASSE_ID]=1;
        return 1;
}

// for xGFW
function xGfw_Kvdb($ip = "127.0.0.1") {
	$c = new SaeCounter ();
	if (! $c->get ( 'visitCnt' )) {
		$c->create ( 'visitCnt' );
	}
	if ($c->incr ( 'visitCnt' ) > CNT_CYCLE) {
		$c->set ( 'visitCnt', 0 );
	} else if ($c->get ( 'visitCnt' ) < CNT_CYCLE_WORK) {
		// filtering out and ban the frequency visit ip
		$kv = new SaeKV ();
		$ret = $kv->init ();
		
		if ($ret == false) {
			xGfw_ShowNotice ( 'Unvaliable','KVDB NOT OK' );
		} else {
                	if (defined('XGFW_TEST')) {
				//$ip = xGfw_GetIP ();
				$cnt = $kv->get ( $ip );
				$isbanned = $kv->get ( 'ban_' . $ip );
				$pre_time = $kv->get ( 'pre_time' . $ip );
                  		echo "ip:" . $ip . "count:" . $cnt . "banned?:" . $isbanned . "pre_time:" . $pre_time."</br>";
			}
			//$ip = xGfw_GetIP ();
			$ret = 0;
			$ret = $kv->get ( 'ban_' . $ip );
			if ($ret) {
				xGfw_ShowNotice ( 'ddos_reflect','ban_' . $ip.' in KVDB BAN list' );
			}
			$pre_time = time ();
			if (! $kv->get ( 'pre_time' . $ip )) {
				$kv->set ( 'pre_time' . $ip, time () );
			} else {
				$pre_time = $kv->get ( 'pre_time' . $ip );
			}
			if (time () < $pre_time + TIME_GAP) {
				$ret = $kv->get ( $ip );
				if (! $ret) {
					$kv->set ( $ip, 1 );
				} else {
					$ret = $kv->get ( $ip );
					$ret ++;
					if ($ret >= LIMIT) {
						$kv->set ( 'ban_' . $ip, 1 );
						xGfw_ShowNotice ( 'ddos_reflect','ban_' . $ip.'first dump in KVDB BAN list' );
					}
					$kv->set ( $ip, $ret );
				}
			} else {
				$kv->set ( $ip, 1 );
				$kv->set ( 'pre_time' . $ip, time () );
			}
		}
	
	}

}
function xGfw_BANB_Http_Defined($url="",$refer="",$ua = ""){
  	if($url == "")
        	$url= $_SERVER["REQUEST_URI"];
        if($refer=="")
        	$refer = $_SERVER["HTTP_REFERER"];
        if($ua == "")
        	$ua =  $_SERVER["HTTP_USER_AGENT"];
  	if(defined('XGFW_TEST')){
  		echo "BAN_BY_HTTP_DEFINED PROCESSING</br>";
          echo "url:".$url.";</br>refer:".$refer.";</br>ua:".$ua.";</br>";
        }
        if(strstr($refer,QUICK_BAN_REFER)){
                xGfw_ShowNotice ( 'ddos_reflect','<strong style="background:black">'.$refer.'</strong>'.' refer ban match with strstr');
        }
          
  	if(BAN_BY_URL){
          	foreach($GLOBALS["xgfw_ban_urls"] as $key=>$val){
                  	if(preg_match($val,$url)){
                          xGfw_ShowNotice ( 'ddos_reflect','<strong style="background:black">'.$url.'</strong>'.' url ban match by rule'.'<strong style="background:black">'.$val.'</strong>' );
                        }
                        if(defined('XGFW_TEST')){
                          echo "url ban rule:".'<strong style="color:white;background:black">'.$val.'</strong>'." not match</br>";
                        }
          	}
        	
        }
  	if(BAN_BY_REFER){
          	foreach($GLOBALS["xgfw_ban_refers"] as $key =>$val){
          		if(preg_match($val,$refer)){
                  		xGfw_ShowNotice ( 'ddos_reflect','<strong style="background:black">'.$refer.'</strong>'.' refer ban match' );
                        }
                        if(defined('XGFW_TEST')){
                          echo "refer ban rule:".'<strong style="color:white;background:black">'.$val.'</strong>'." not match</br>";
                        }
                }
        }
        if(BAN_BY_UA){
          	foreach($GLOBALS["xgfw_ban_uas"] as $key =>$val){
          		if(preg_match($val,$ua)){
                  		xGfw_ShowNotice ( 'ddos_reflect','<strong style="background:black">'.$ua.'</strong>'.' ua ban match' );
                        }
                        if(defined('XGFW_TEST')){
                          echo "ua ban rule:".'<strong style="color:white;background:black">'.$val.'</strong>'." not match</br>";
                        }
                }
        }
        if(defined('XGFW_TEST')){
  		echo "BAN_BY_HTTP_DEFINED END</br>";
        }

}
// for xGFW
function xGfw_MmcWithCounter($ip = "127.0.0.1") {
	$c = new SaeCounter ();
	if (! $c->get ( 'visitCnt_mmc' )) {
		$c->create ( 'visitCnt' );
	}
  	if(! $c->get('ipListNum')){
  		$c->create('ipListNum');
          //$c->set('ipListNum',1);
        }
	if ($c->incr ( 'visitCnt_mmc' ) > CNT_CYCLE_MMC) {
		$c->set ( 'visitCnt_mmc', 0 );
	} else if ($c->get ( 'visitCnt_mmc' ) < CNT_CYCLE_WORK_MMC) {
		// filtering out and ban the frequency visit ip
                        echo "_SESSION[banned]=".$_SESSION[SESSION_BAN_ID]."</br>";
			if ($_SESSION[SESSION_BAN_ID]==1) {
				xGfw_ShowNotice ( 'ddos_reflect','ban_' . $ip.' in Session mmc' );
			}
			$pre_time = time ();
			if (!isset($_SESSION['pre_time']) || $_SESSION['pre_time']=='') {
				$_SESSION['pre_time']=time();
			} else {
				$pre_time = $_SESSION['pre_time'];
			}
			if (time () < $pre_time + TIME_GAP) {
				prepareInCnt($c,$ip);
				if ($c->incr($ip) >= LIMIT_SESSION) {
                                	$_SESSION[SESSION_BAN_ID] = 1;
					xGfw_ShowNotice ( 'ddos_reflect','ban_' . $ip.'first Banned with session' );
				}
				
			} else {
                        	prepareInCnt($c,$ip);
				$c->set($ip,1);
				$_SESSION['pre_time']=time();
			}

	
	}
        if (defined('XGFW_TEST')) {
          echo "xGfw_MmcWithCounter passed</br>";
          echo "_SESSION[pretime]=".$_SESSION['pre_time']."</br>";
          echo "_SESSION[banned]=".$_SESSION[SESSION_BAN_ID]."</br>";
          echo "IP_COUNT_BY_Counter:".$c->get($ip)."</br>";
          echo "ipListNum:".$c->get('ipListNum')."</br>";
          echo "_SESSION_PASS".$_SESSION[SESSION_PASSE_ID]."</br>";
	}
}



function prepareInCnt($c,$ip){
	$ret = $c->get($ip);
  	if(!$ret){
          	if($c->incr('ipListNum')>MAX_COUNTER){
          		queue_pop();
                }
                $c->create($ip);
                queue_push($ip);
  	}
}

function queue_pop(){
	$mmc=memcache_init();
  	if($mmc==false){
  		xGfw_ShowNotice ( 'Unvaliable','MMC NOT OK' );
        }else{
        	$queue = memcache_get($mmc,"xgfw_iplist");
                array_shift($queue);
                memcache_set($mmc,"xgfw_iplist",$queue);
        }
}
function queue_push($ip){
	$mmc=memcache_init();
  	if($mmc==false){
  		xGfw_ShowNotice ( 'Unvaliable','MMC NOT OK' );
        }else{
        	$queue = memcache_get($mmc,"xgfw_iplist");
          	if($queue ==''){
                	$queue = array($ip);
                }else{
                	array_push($queue,$ip);
                }
                memcache_set($mmc,"xgfw_iplist",$queue);
        }
}


function pass_by_session(){
  	if($_SESSION[SESSION_PASSE_ID] ==1){
  		return true;
  	}
  	return false;
}
function ban_by_session(){
	if ($_SESSION[SESSION_BAN_ID]==1) {
		xGfw_ShowNotice ( 'ddos_reflect','ban_' . $ip.' in Session' );
	}
}

function sessionDestroy(){
  //@session_destroy();
  $_SESSION[SESSION_PASSE_ID] =0;
  $_SESSION[SESSION_BAN_ID] = 0;
  echo "Session Destoryed</br>".$_SESSION[SESSION_BAN_ID];
}



?>