<?php

$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => PKG_NAME,
    'description' => 'AlohaX is a very front-end editing snippet powered by Aloha.',
    'snippet' => getSnippetContent($sources['snippets'].'alohax.snippet.php'),
));
//$properties = include $sources['data'].'properties/properties.cmcampers.php';
//$snippets[1]->setProperties($properties);

return $snippets;

?>