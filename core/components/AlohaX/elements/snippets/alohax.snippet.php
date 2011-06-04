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
$saveOnBlur = $modx->getOption('saveOnBlur', $scriptProperties, 0);
$fields = $modx->getOption('editableFields', $scriptProperties, '*');

//load this up so we can use it on the front end for storage.
$resource = $modx->getObject('modResource', $modx->resourceIdentifier);

$dirtyFields = $resource->toArray();
foreach($dirtyFields as $fieldName => $val){
	$dirtyFields[$fieldName] = 0;
}

if($fields == '*'){
	//get resource fields
	$fields = $resource->toArray();
	$fields = array_keys($fields);
} else {
	$fields = explode(',', $fields);
}

$statusBarAssets = array(
	'jquery.statusbar.js'
	,'css/style.css'
);

$alohaAssets = array(
	'aloha.js'
	,'plugins/com.gentics.aloha.plugins.Format/plugin.js'
	,'plugins/com.gentics.aloha.plugins.Table/plugin.js'
	,'plugins/com.gentics.aloha.plugins.List/plugin.js'
	,'plugins/com.gentics.aloha.plugins.Link/plugin.js'
	,'plugins/com.gentics.aloha.plugins.HighlightEditables/plugin.js'
	,'plugins/com.gentics.aloha.plugins.TOC/plugin.js'
	,'plugins/com.gentics.aloha.plugins.Link/LinkList.js'
	,'plugins/com.gentics.aloha.plugins.Paste/plugin.js'
	,'plugins/com.gentics.aloha.plugins.Paste/wordpastehandler.js'
	,'alohax.js'
);

//$alohaAssets = array('alohax.js');

$siteId = $_SESSION["modx.mgr.user.token"];
//used to be $modx->siteId?

$vars = array(
	'assetsPath' => $assetsPath
	,'HTTP_MODAUTH' => $siteId
	,'saveOnBlur' => $saveOnBlur
	,'resourceIdentifier' => $modx->resourceIdentifier
	,'fields' => $fields
	,'resource' => $resource->toArray()
	,'dirtyFields' => $dirtyFields
);

$json = $modx->toJSON($vars);
$modx->regClientStartupHTMLBlock(
	'<script type="text/javascript">
		var alohaXSettings = ' . $json . ';
	</script>'
);

foreach($alohaAssets as $script){
	//register all scripts in order.
	$modx->regClientStartupScript($assetsPath . 'aloha/' . $script);
}

foreach($statusBarAssets as $script){
	//register all scripts in order.
	if(end(explode('.', $script)) == 'js'){
		$modx->regClientStartupScript($assetsPath . 'statusbar/' . $script);
	} else {
		$modx->regClientCSS($assetsPath . 'statusbar/' . $script);
	}
	
}



