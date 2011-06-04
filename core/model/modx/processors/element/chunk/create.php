<?php
/**
 * Creates a chunk.
 *
 * @param string $name The name of the chunk.
 * @param string $description (optional) The description of the chunk.
 * @param integer $category The category the chunk is assigned to.
 * @param string $snippet The code of the chunk.
 * @param boolean $locked Whether or not the chunk can only be accessed by
 * administrators.
 * @param json $propdata A json array of properties to store.
 *
 * @package modx
 * @subpackage processors.element.chunk
 */
if (!$modx->hasPermission('new_chunk')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('chunk');

/* default values */
if (empty($scriptProperties['name'])) $scriptProperties['name'] = $modx->lexicon('chunk_untitled');

/* verify chunk with that name does not already exist */
$nameExists = $modx->getObject('modChunk',array('name' => $scriptProperties['name']));
if ($nameExists != null) {
    $modx->error->addField('name',$modx->lexicon('chunk_err_exists_name',array(
        'name' => $scriptProperties['name'],
    )));
}
/* category */
if (!empty($scriptProperties['category'])) {
    $category = $modx->getObject('modCategory',array('id' => $scriptProperties['category']));
    if ($category == null) $modx->error->addField('category',$modx->lexicon('category_err_nf'));
    if (!$category->checkPolicy('add_children')) return $modx->error->failure($modx->lexicon('access_denied'));
}
/* set fields for the new chunk */
$chunk = $modx->newObject('modChunk');
$chunk->fromArray($scriptProperties);
$chunk->set('locked',!empty($scriptProperties['locked']));

/* set properties */
$properties = null;
if (isset($scriptProperties['propdata'])) {
    $properties = $scriptProperties['propdata'];
    $properties = $modx->fromJSON($properties);
}
if (is_array($properties)) $chunk->setProperties($properties);

if (!$chunk->validate()) {
    $validator = $chunk->getValidator();
    if ($validator->hasMessages()) {
        foreach ($validator->getMessages() as $message) {
            $modx->error->addField($message['field'], $modx->lexicon($message['message']));
        }
    }
}

/* if has any errors, return */
if ($modx->error->hasError()) {
    return $modx->error->failure();
}

/* invoke OnBeforeChunkFormSave event */
$OnBeforeChunkFormSave = $modx->invokeEvent('OnBeforeChunkFormSave',array(
    'mode'  => modSystemEvent::MODE_NEW,
    'id' => 0,
    'data' => $chunk->toArray(),
    'chunk' => &$chunk,
));
if (is_array($OnBeforeChunkFormSave)) {
    $canSave = false;
    foreach ($OnBeforeChunkFormSave as $msg) {
        if (!empty($msg)) {
            $canSave .= $msg."\n";
        }
    }
} else {
    $canSave = $OnBeforeChunkFormSave;
}
if (!empty($canSave)) {
    return $modx->error->failure($canSave);
}
/* save chunk */
if ($chunk->save() == false) {
    return $modx->error->failure($modx->lexicon('chunk_err_save'));
}

/* invoke OnChunkFormSave event */
$modx->invokeEvent('OnChunkFormSave',array(
    'mode' => modSystemEvent::MODE_NEW,
    'id'   => $chunk->get('id'),
    'chunk' => &$chunk,
));

/* log manager action */
$modx->logManagerAction('chunk_create','modChunk',$chunk->get('id'));

/* empty cache */
if (!empty($scriptProperties['clearCache'])) {
    $modx->cacheManager->refresh();
}

return $modx->error->success('',$chunk->get(array('id', 'name', 'description', 'locked', 'category')));