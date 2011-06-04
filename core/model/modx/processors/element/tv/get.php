<?php
/**
 * Gets a TV
 *
 * @param integer $id The ID of the TV
 *
 * @package modx
 * @subpackage processors.element.tv
 */
if (!$modx->hasPermission('view_tv')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('tv');

if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('tv_err_ns'));
$tv = $modx->getObject('modTemplateVar',$scriptProperties['id']);
if (empty($tv)) return $modx->error->failure($modx->lexicon('tv_err_nfs',array('id' => $scriptProperties['id'])));

if (!$tv->checkPolicy('view')) {
    return $modx->error->failure($modx->lexicon('access_denied'));
}

$tv->set('els',$tv->get('elements'));
$properties = $tv->get('properties');
if (!is_array($properties)) $properties = array();

$data = array();
foreach ($properties as $property) {
    $data[] = array(
        $property['name'],
        $property['desc'],
        $property['type'],
        $property['options'],
        $property['value'],
        $property['lexicon'],
        false, /* overridden set to false */
        $property['desc_trans'],
    );
}

$tv->set('data','(' . $modx->toJSON($data) . ')');

return $modx->error->success('',$tv);