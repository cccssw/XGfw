<?php
/**
 * 基于快速排序的二分查找PHP实现
 * Author:Cyrec 
 * Link: http://cc.ecjtu.net/posts/php-binary-search-base-on-quick-sort
 */

function xGfw_swap(&$a, &$b) {
	list ( $a, $b ) = array (
			$b,
			$a 
	);
}

/**
 * 快速排序的交换代码实现,将数组分为有序的两堆,并返回中间位置
 *
 * @param array $arr        	
 * @param int $l        	
 * @param int $r        	
 * @return int $m 返回大小两堆的中间位置,然后再递归调整
 */
function xGfw_partition(&$arr, $l, $r) {
	$base = $arr [$l];
	$i = $l + 1;
	$j = $r;
	while ( $i <= $j ) { // 逐个将比base大和比base小的数字掉换,直到左右指针相等
		while ( $i <= $j && $arr [$i] <= $base )
			++ $i;
		while ( $i <= $j && $arr [$j] > $base )
			-- $j;
		if ($i < $j)
			xGfw_swap ( $arr [$i], $arr [$j] );
	}
	xGfw_swap ( $arr [$l], $arr [$j] ); // 交换后左边的比$base小,右边的大
	return $j;
}

/**
 * PHP快速排序递归实现
 *
 * @param
 *        	array &$arr 排序数组
 * @param int $l
 *        	排序的起始位置
 * @param int $r
 *        	结束位置
 */
function xGfw_quick_sort(&$arr, $l = null, $r = null) {
	if (is_null ( $l ))
		$l = 0;
	if (is_null ( $r ))
		$r = count ( $arr ) - 1;
	if ($l < $r) {
		$m = xGfw_partition ( $arr, $l, $r );
		xGfw_quick_sort ( $arr, $l, $m - 1 );
		xGfw_quick_sort ( $arr, $m + 1, $r );
	}
}

/**
 * PHP二分查找实现代码
 *
 * @param array $arr
 *        	有序的数字序列
 * @param int $s
 *        	查找的关键字
 * @param int $l
 *        	查找的起始位置
 * @param int $r
 *        	查找的结束位置
 * @return unknown
 */
function xGfw__binary_search($arr, $s, $l, $r) {
	if ($l <= $r) {
		$m = ($r + $l) >> 1; // echo $m;exit;
		if ($s == $arr [$m]) {
			return true;
		} elseif ($s > $arr [$m]) {
			return xGfw__binary_search ( $arr, $s, $m + 1, $r );
		} elseif ($s < $arr [$m]) {
			return xGfw__binary_search ( $arr, $s, $l, $m - 1 );
		}
	}
	return false;
}
/**
 * PHP二分查找,先利用快速排序将待查找数组有序化
 *
 * @param array $arr
 *        	待查找的数组
 * @param int $s
 *        	查找的关键字
 * @return bool 返回查找结果 true找到,false未找到
 */
function xGfw_binary_search($arr, $s) {
	xGfw_quick_sort ( $arr );
	$l = 0;
	$r = count ( $arr ) - 1;
	return xGfw__binary_search ( $arr, $s, $l, $r );
}

/**
 * PHP二分查找,先利用快速排序将待查找数组有序化
 *
 * @param array $arr
 *        	待查找的数组
 * @param int $s
 *        	查找的关键字
 * @return bool 返回查找结果 true找到,false未找到
 */
function xGfw_binary_search_sorted($arr, $s) {
	// xGfw_quick_sort($arr);
	$l = 0;
	$r = count ( $arr ) - 1;
	return xGfw__binary_search ( $arr, $s, $l, $r );
}
/*
 * $arr = array(4,2,3,5,7,6); $num = 5; if (binary_search($arr, $num)) { echo
 * "Find the num: $num !"; }else{ echo "Not find the num: $num !"; }
 */



function xGfw_ShowNotice($type = 'frequent',$reason="frequent reason") {
  	if(defined('XGFW_TEST')){
          echo "<p style='color:red;'>Stopped,By reason:<i>".$reason."</i></p>";
        }else{
        	if ($type == 'Unvaliable') {
			echo "Unvaliable";
			exit ();
		}
		if ($type == 'ddos_reflect') {
			if (DDOS_REFLECT_ENABLE == 1) {
				header ( 'Location: ' . DDOS_REFLECT );
			} else {
				header ( 'Location: ' . FREQUENT_URL );
			}
		exit ();
		}
                if ($type == 'frequent') {
			header ( 'Location: ' . FREQUENT_URL );
			exit ();
		}
		header ( 'Location: ' . SERVERDOWN_URL );
		exit ();
        }
        
}

function xGfw_GetIP() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return ($ip);
}
