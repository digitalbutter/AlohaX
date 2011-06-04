<?php
/**
 * Update a content type from the grid. Sent through JSON-encoded 'data'
 * parameter.
 *
 * @param integer $id The ID of the content type
 * @param string $name The new name
 * @param string $description (optional) A short description
 * @param string $mime_type The MIME type for the content type
 * @param string $file_extensions A list of file extensions associated with this
 * type
 * @param string $headers Any headers to be sent with resources with this type
 * @param boolean $binary If true, will be sent as binary data
 *
 * @package modx
 * @subpackage processors.system.contenttype
 */
if (!$modx->hasPermission('content_types')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('content_type');

/* get content type */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('content_type_err_ns'));
$contentType = $modx->getObject('modContentType',$scriptProperties['id']);
if ($contentType == null) {
    return $modx->error->failure($modx->lexicon('content_type_err_nfs',array(
        'id' => $scriptProperties['id'],
    )));
}

/* save content type */
$scriptProperties['binary'] = !empty($scriptProperties['binary']) ? true : false;
$contentType->fromArray($scriptProperties);
$refresh = $contentType->isDirty('file_extensions') && $modx->getCount('modResource', array('content_type' => $contentType->get('id')));
if ($contentType->save() == false) {
    $modx->error->checkValidation($contentType);
    return $modx->error->failure($modx->lexicon('content_type_err_save'));
}

if ($refresh) {
    $modx->call('modResource', 'refreshURIs', array(&$modx));
}

/* log manager action */
$modx->logManagerAction('content_type_save','modContentType',$contentType->get('id'));

return $modx->error->success('',$contentType);