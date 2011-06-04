<?php

$saved = false;

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	$resource->set($scriptProperties['field'], $scriptProperties['content']);
	$saved = $resource->save();
}

if($saved){
	$modx->cacheManager->clearCache();
	return $modx->error->success("Successfully saved " . $scriptProperties['field'] . " field.");
}

return $modx->error->failure("Error! The " . $scriptProperties['field'] . " wasn't saved properly.");