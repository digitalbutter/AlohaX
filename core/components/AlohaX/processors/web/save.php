<?php

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	$resource->set($scriptProperties['field'], $scriptProperties['content']);
	$resource->save();
	return $modx->error->success();
}

return $modx->error->failure();