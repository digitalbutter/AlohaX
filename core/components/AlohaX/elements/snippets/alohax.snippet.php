<?php

/*

Purpose of this snippet is to inject javascripts that will allow editing on the front end.
For now, only managers will have access unless the below is modified.

*/

//#Auth

if(!$modx->getAuthenticatedUser('mgr')){
	return;
}

//#End Auth

//get options
$assetsPath = $modx->getOption('assetsPath', $scriptProperties, 'assets/components/AlohaX/');
$saveOnBlur = $modx->getOption('saveOnBlur', $scriptProperties, 1);
$registerJquery = $modx->getOption('registerJquery', $scriptProperies, 0);

$jbarAssets = array(
	'jquery.bar.js'
);

if($registerJquery){
	$jbarAssets[] = 'jquery-1.3.2.js'
}

$alohaAssets = array(
	'aloha.js'
	,'com.gentics.aloha.plugins.Format/plugin.js'
	,'com.gentics.aloha.plugins.Table/plugin.js'
	,'com.gentics.aloha.plugins.List/plugin.js'
	,'com.gentics.aloha.plugins.Link/plugin.js'
	,'com.gentics.aloha.plugins.HighlightEditables/plugin.js'
	,'com.gentics.aloha.plugins.TOC/plugin.js'
	,'com.gentics.aloha.plugins.Link/delicious.js'
	,'com.gentics.aloha.plugins.Link/LinkList.js'
	,'com.gentics.aloha.plugins.Paste/plugin.js'
	,'com.gentics.aloha.plugins.Paste/wordpastehandler.js'
	,'image.js'
	,'alohax.js'
);

$vars = array(
	'assetsPath' => $assetsPath
	,'HTTP_MODAUTH' => $modx->site_id
	,'saveOnBlur' => $saveOnBlur
);

foreach($jbarAssets as $script){
	//register all scripts in order.
	$modx->regClientStartupScript($assetsPath . 'jbar/' . $script);
}

foreach($alohaAssets as $script){
	//register all scripts in order.
	$modx->regClientStartupScript($assetsPath . 'aloha/' . $script);
}

