<?php
/**
 * Gets a dynamic toolbar for the Resource tree.
 *
 * @package modx
 * @subpackage processors.layout.tree.resource
 */
if (!$modx->hasPermission('resource_tree')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('resource');

$p = $modx->getOption('manager_url').'templates/'.$modx->getOption('manager_theme').'/images/restyle/icons/';

$actions = $modx->request->getAllActionIDs();

$items = array();
$items[] = array(
    'icon' => $p.'arrow_down.png',
    'tooltip' => $modx->lexicon('expand_tree'),
    'handler' => 'this.expandAll',
);
$items[] = array(
    'icon' => $p.'arrow_up.png',
    'tooltip' => $modx->lexicon('collapse_tree'),
    'handler' => 'this.collapseAll',
);
$items[] = '-';
if ($modx->hasPermission('new_document')) {
    $items[] = array(
        'icon' => $p.'folder_page_add.png',
        'tooltip' => $modx->lexicon('document_new'),
        'handler' => 'new Function("this.redirect(\"index.php?a='.$actions['resource/create'].'\");");',
    );
    $items[] = array(
        'icon' => $p.'page_white_link.png',
        'tooltip' => $modx->lexicon('add_weblink'),
        'handler' => 'new Function("this.redirect(\"index.php?a='.$actions['resource/create'].'&class_key=modWebLink\");");',
    );
    $items[] = array(
        'icon' => $p.'page_white_copy.png',
        'tooltip' => $modx->lexicon('add_symlink'),
        'handler' => 'new Function("this.redirect(\"index.php?a='.$actions['resource/create'].'&class_key=modSymLink\");");',
    );
    $items[] = array(
        'icon' => $p.'page_white_gear.png',
        'tooltip' => $modx->lexicon('static_resource_new'),
        'handler' => 'new Function("this.redirect(\"index.php?a='.$actions['resource/create'].'&class_key=modStaticResource\");");',
    );
    $items[] = '-';
}
$items[] = array(
    'icon' => $p.'refresh.png',
    'tooltip' => $modx->lexicon('refresh_tree'),
    'handler' => 'this.refresh',
);
$items[] = array(
    'icon' => $p.'unzip.gif',
    'tooltip' => $modx->lexicon('show_sort_options'),
    'handler' => 'this.showFilter',
);
if ($modx->hasPermission('purge_deleted')) {
    $items[] = '-';
    $items[] = array(
        'icon' => $p.'trash.png',
        'tooltip' => $modx->lexicon('empty_recycle_bin'),
        'handler' => 'this.emptyRecycleBin',
    );
}


$modx->invokeEvent('OnResourceToolbarLoad',array(
    'items' => &$items,
));

return $modx->error->success('',$items);