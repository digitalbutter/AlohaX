<?php
/**
 * Remove a directory
 *
 * @param string $dir The directory to remove
 * @param boolean $prependPath (optional) If true, will prepend rb_base_dir to
 * the final path
 *
 * @package modx
 * @subpackage processors.browser.directory
 */
if (!$modx->hasPermission('directory_remove')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('file');

if (empty($scriptProperties['dir'])) return $modx->error->failure($modx->lexicon('file_folder_err_ns'));

/* get working context */
$wctx = isset($scriptProperties['wctx']) && !empty($scriptProperties['wctx']) ? $scriptProperties['wctx'] : '';
if (!empty($wctx)) {
    $workingContext = $modx->getContext($wctx);
    if (!$workingContext) {
        return $modx->error->failure($modx->error->failure($modx->lexicon('permission_denied')));
    }
} else {
    $workingContext =& $modx->context;
}

$modx->getService('fileHandler','modFileHandler', '', array('context' => $workingContext->get('key')));
$root = $modx->fileHandler->getBasePath(false);
if ($workingContext->getOption('filemanager_path_relative',true)) {
    $root = $workingContext->getOption('base_path','').$root;
}

/* in case rootVisible is true */
$path = str_replace(array(
    'root/',
    'undefined/',
),'',$scriptProperties['dir']);

/* instantiate modDirectory object */
$directory = $modx->fileHandler->make($root.$path);

/* validate and check permissions on directory */
if (!($directory instanceof modDirectory)) return $modx->error->failure($modx->lexicon('file_folder_err_invalid'));
if (!$directory->isReadable() || !$directory->isWritable()) {
    return $modx->error->failure($modx->lexicon('file_folder_err_perms_remove'));
}

/* remove the directory */
$result = $directory->remove();
if ($result == false) {
    return $modx->error->failure($modx->lexicon('file_folder_err_remove'));
}

$modx->logManagerAction('directory_remove','',$directory->getPath());

return $modx->error->success();
