<?php
/**
 * Loads the welcome page
 *
 * @package modx
 * @subpackage manager
 */
if (!$modx->hasPermission('home')) return $modx->error->failure($modx->lexicon('permission_denied'));

$modx->lexicon->load('configcheck');
$modx->smarty->assign('site_name',$modx->getOption('site_name'));

/* assign current time message */
$modx->smarty->assign('online_users_msg',$modx->lexicon('onlineusers_message',array(
    'curtime' => strftime('%X', time()+$modx->getOption('server_offset_time',null,0))
)));

/* do some config checks */
$success = include_once $modx->getOption('processors_path') . 'system/config_check.inc.php';
if (!$success) {
    $config_display = true;
    $modx->smarty->assign('config_check_results',$config_check_results);
} else {
    $config_display = false;
}

$serverOffset = $modx->getOption('server_offset_time',null,0);

/* user info */
$profile = $modx->user->getOne('Profile');
if ($profile && $profile->get('lastlogin') != '') {
    $offset = $serverOffset * 60 * 60;
    $previous_login = strftime('%b %d, %Y %I:%M %p',$profile->get('lastlogin')+$offset);
} else {
    $previous_login = $modx->lexicon('not_set');
}
$modx->smarty->assign('previous_login',$previous_login);

/*
 * @todo refactor online user tracking
 */
$timetocheck = (time()-(60*20))+$serverOffset;
$c = $modx->newQuery('modActiveUser');
$c->where(array('lasthit:>' => $timetocheck));
$c->sortby($modx->getSelectColumns('modActiveUser','modActiveUser','',array('username')),'ASC');
$ausers = $modx->getCollection('modActiveUser',$c);
include_once $modx->getOption('processors_path'). 'system/actionlist.inc.php';
foreach ($ausers as $user) {
	$currentaction = getAction($user->get('action'), $user->get('id'));
	$user->set('currentaction',$currentaction);
	$user->set('lastseen',strftime('%X',$user->lasthit+$serverOffset));
}
$modx->smarty->assign('ausers',$ausers);


/* grab rss feeds */
$modx->loadClass('xmlrss.modRSSParser','',false,true);
$rssparser = new modRSSParser($modx);

$url = $modx->getOption('feed_modx_news');
$newsEnabled = $modx->getOption('feed_modx_news_enabled',null,true);
if (!empty($url) && !empty($newsEnabled)) {
    $rss = $rssparser->parse($url);
    foreach (array_keys($rss->items) as $key) {
    	$item= &$rss->items[$key];
        $item['pubdate'] = strftime('%c',$item['date_timestamp']);
    }
    $modx->smarty->assign('newsfeed',$rss->items);
}

$url = $modx->getOption('feed_modx_security');
$securityEnabled = $modx->getOption('feed_modx_security_enabled',null,true);
if (!empty($url) && !empty($securityEnabled)) {
    $rss = $rssparser->parse($url);
    foreach (array_keys($rss->items) as $key) {
    	$item= &$rss->items[$key];
        $item['pubdate'] = strftime('%c',$item['date_timestamp']);
    }
    $modx->smarty->assign('securefeed',$rss->items);
}

/* do manager dashboard code here, remove portal extjs widgets and do so
 * via straight HTML, with maybe only the panel in modext.
 */

$hasViewDocument = $modx->hasPermission('view_document');
$hasViewUser = $modx->hasPermission('view_user');

/* load JS scripts for page */
$modx->regClientStartupScript($modx->getOption('manager_url').'assets/modext/widgets/modx.panel.welcome.js');
$modx->regClientStartupScript($modx->getOption('manager_url').'assets/modext/widgets/security/modx.grid.user.recent.resource.js');
$modx->regClientStartupScript($modx->getOption('manager_url').'assets/modext/sections/welcome.js');
$modx->regClientStartupHTMLBlock('
<script type="text/javascript">
// <![CDATA[
Ext.onReady(function() {
    MODx.hasViewDocument = "'.($hasViewDocument ? 1 : 0).'";
    MODx.hasViewUser = "'.($hasViewUser ? 1 : 0).'";
    MODx.load({
        xtype: "modx-page-welcome"
        ,site_name: "'.htmlentities($modx->getOption('site_name')).'"
        ,displayConfigCheck: '.($config_display ? 'true': 'false').'
        ,user: "'.$modx->user->get('id').'"
        ,newsEnabled: "'.$newsEnabled.'"
        ,securityEnabled: "'.$securityEnabled.'"
    });
});
// ]]>
</script>');

if ($modx->getOption('welcome_screen',null,false)) {
    $url = $modx->getOption('welcome_screen_url',null,'http://misc.modx.com/revolution/welcome.20.html');
    $modx->regClientStartupHTMLBlock('<script type="text/javascript">
// <![CDATA[
Ext.onReady(function() { MODx.loadWelcomePanel("'.$url.'"); });
// ]]>
</script>');
    $setting = $modx->getObject('modSystemSetting','welcome_screen');
    if ($setting) {
        $setting->set('value',false);
        $setting->save();
    }
    $setting = $modx->getObject('modUserSetting',array(
        'key' => 'welcome_screen',
        'user' => $modx->user->get('id'),
    ));
    if ($setting) {
        $setting->set('value',false);
        $setting->save();
    }
    $modx->reloadConfig();
}
$modx->smarty->assign('_pagetitle',$modx->lexicon('dashboard'));
$this->checkFormCustomizationRules();
return $modx->smarty->fetch('welcome.tpl');