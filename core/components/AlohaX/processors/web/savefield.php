<?php

$saved = false;

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	foreach($scriptProperties as $fieldname => $value){
		//this is a temporary measure to avoid whitespace added by aloha
		$scriptProperties[$fieldname] = trim($value);
	}
	if(array_key_exists($scriptProperties['field'], $resource->toArray())){
		$resource->set($scriptProperties['field'], $scriptProperties['content']);
	} else {
		//its a TV, maybe. if not it will go unsaved.
		$tvs = $resource->getMany('TemplateVars');
		foreach($tvs as $tvId => $tv){
			if($tv->get('name') == $scriptProperties['field']){
				//save the tv value
				$tv->setValue($resource->get('id'), $scriptProperties['content']);
				$saved = $tv->save();	
			}
		}
	}
	$saved = $resource->save();
}

if($saved){
	$modx->cacheManager->clearCache();
	return $modx->error->success("Successfully saved " . $scriptProperties['field'] . " field.");
}

return $modx->error->failure("Error! The " . $scriptProperties['field'] . " wasn't saved properly.");