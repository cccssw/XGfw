<?php
define('SERVERDOWN_URL','http://rqdashi.diandian.com/404/');
define('DDOS_REFLECT','http://www.csuboy.com/read-renqi-tid-1046024.html');
define('FREQUENT_URL','http://rqdashi.diandian.com/frequent');
define('DDOS_REFLECT_ENABLE',0);
define('CNT_CYCLE',1000);//pv
define('CNT_CYCLE_WORK',600);//pv

define('CNT_CYCLE_MMC',1000);//pv
define('CNT_CYCLE_WORK_MMC',1000);//pv

define('MAX_COUNTER',95);
define('SESSION_PASSE_ID','passed_by_session');
define('SESSION_BAN_ID','ban_by_session');
define('LIMIT',18);
define('LIMIT_SESSION',18);
define('TIME_GAP',10);//seconds


define('METHOD_WHITE','POST');
define('BAN_BY_URL',0);
$GLOBALS["xgfw_ban_urls"] = array("/site\/status\?/",);
define('BAN_BY_REFER',0);
$GLOBALS["xgfw_ban_refers"] = array("/./",);
define('QUICK_BAN_REFER','.');
define('BAN_BY_UA',0);
$GLOBALS["xgfw_ban_uas"] = array("/wua/",);

