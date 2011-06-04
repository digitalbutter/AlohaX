<?php

$saved = false;

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	foreach($scriptProperties['resource'] as $fieldname => $value){
		//this is a temporary measure to avoid whitespace added by aloha and slashes added by serialise function.
		$scriptProperties['resource'][$fieldname] = stripslashes(trim($value));
	}
	$resource->fromArray($scriptProperties['resource']);
	//save resource
	$saved = $resource->save();
	$tvs = $resource->getMany('TemplateVars');
	foreach($tvs as $tvId => $tv){
		if(array_key_exists($tv->get('name'), $scriptProperties['resource'])){
			//save a tv if the value is passed.
			$tv->setValue($resource->get('id'), $scriptProperties['resource'][$tv->get('name')]);
			$saved = $tv->save();
		}
	}
}

if($saved){
	$modx->cacheManager->clearCache();
	return $modx->error->success("This page has now been saved.");
}

return $modx->error->failure("This page could not be saved.");