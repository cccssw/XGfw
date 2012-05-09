<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/xgfw_func.php');
$xgfw_ban = require(dirname(__FILE__).'/black_list_sorted.php');
$xgfw_pass = require(dirname(__FILE__).'/white_list_sorted.php');

@session_start();
$white =false;
$ip = xGfw_GetIP();
$white = pass_by_session();
if(!$white){
	xGfw_BANB_Http_Defined();        
	$white = xGfw_HardCode_Ban($ip,$xgfw_ban,$xgfw_pass);
        if(!$white){
          xGfw_Kvdb($ip);//ban by kvdb action
	}
}





//ban functions
function xGfw_HardCode_Ban($ip="127.0.0.1",$ban,$pass){
	$ips = explode( ',' ,$ip );
	if( !xGfw_binary_search_sorted($pass,$ips[0]) ){
          	ban_by_session();//GFW FILTER
          	xGfw_MmcWithCounter($ip);//GFW FILTER
      	  	if(xGfw_binary_search_sorted($ban,$ips[0])){
          		$_SESSION[SESSION_BAN_ID] = 1;
          		xGfw_ShowNotice ( 'ddos_reflect' );
          		exit;
          	}
          	return 0;
	}
        $_SESSION[SESSION_PASSE_ID]=1;
        $_SESSION[SESSION_BAN_ID] = 0;
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
			xGfw_ShowNotice ( 'Unvaliable' );
		} else {
			//$ip = xGfw_GetIP ();
			$ret = 0;
			$ret = $kv->get ( 'ban_' . $ip );
			if ($ret) {
				xGfw_ShowNotice ();
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
                                  //$ret = $kv->get ( $ip );
					$ret ++;
					if ($ret >= LIMIT) {
						$kv->set ( 'ban_' . $ip, 1 );
						xGfw_ShowNotice ();
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
  	if(strstr($refer,QUICK_BAN_REFER)){
                xGfw_ShowNotice ( 'ddos_reflect');
        }
          
  	if(BAN_BY_URL){
          	foreach($GLOBALS["xgfw_ban_urls"] as $key=>$val){
                  	if(preg_match($val,$url)){
                          xGfw_ShowNotice ( 'ddos_reflect');
                        }
          	}
        	
        }
  	if(BAN_BY_REFER){
          	foreach($GLOBALS["xgfw_ban_refers"] as $key =>$val){
          		if(preg_match($val,$refer)){
                  		xGfw_ShowNotice ( 'ddos_reflect');
                        }

                }
        }
        if(BAN_BY_UA){
          	foreach($GLOBALS["xgfw_ban_uas"] as $key =>$val){
          		if(preg_match($val,$ua)){
                  		xGfw_ShowNotice ( 'ddos_reflect');
                        }

                }
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
			if ($_SESSION[SESSION_BAN_ID]==1) {
				xGfw_ShowNotice ( 'ddos_reflect');
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
					xGfw_ShowNotice ( 'ddos_reflect');
				}
				
			} else {
                        	prepareInCnt($c,$ip);
				$c->set($ip,1);
				$_SESSION['pre_time']=time();
			}

	
	}
}



function prepareInCnt($c,$ip){
	$ret = $c->get($ip);
  	if(!$ret){
          	if($c->incr('ipListNum')>MAX_COUNTER){
          		queue_pop($c);
                  	$c->set('ipListNum',MAX_COUNTER);
                }
                $c->create($ip);
                queue_push($c,$ip);
  	}
}

function queue_pop($c){
	$mmc=memcache_init();
  	if($mmc==false){
  		xGfw_ShowNotice ( 'Unvaliable','MMC NOT OK' );
        }else{
        	$queue = memcache_get($mmc,"xgfw_iplist");
                $c->remove($queue[0]);
                array_shift($queue);
                memcache_set($mmc,"xgfw_iplist",$queue);
                
        }
}
function queue_push($c,$ip){
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
                $c->create($ip);
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
		xGfw_ShowNotice ( 'ddos_reflect');
	}
}

function sessionDestroy(){
  //@session_destroy();
  $_SESSION[SESSION_PASSE_ID] =0;
  $_SESSION[SESSION_BAN_ID] = 0;
  echo "Session Destoryed</br>".$_SESSION[SESSION_BAN_ID];
}

?>