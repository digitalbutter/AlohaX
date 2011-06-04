<?php

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	$resource->set($scriptProperties['field'], $scriptProperties['content']);
	$resource->save();
	return $modx->error->success();
}

$modx->cacheManager->clearCache();

return $modx->error->failure();