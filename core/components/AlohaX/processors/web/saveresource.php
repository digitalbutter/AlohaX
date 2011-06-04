<?php

$saved = false;

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	$resource->fromArray($scriptProperties['resource']);
	$saved = $resource->save();
}

if($saved){
	$modx->cacheManager->clearCache();
	return $modx->error->success("This page has now been saved.");
}

return $modx->error->failure("This page could not be saved.");