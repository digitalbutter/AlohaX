<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

/* handle request */
$path = MODX_BASE_PATH . 'core/components/AlohaX/processors/';
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));