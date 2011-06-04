<?php
/*
 * AholaX
 *
 * Copyright 2011 by Digital Butter <www.butter.com.hk>
 *
 * This file is part of AholaX, a port of Ahola Editor (ahola-editor.com) for MODX Revolution.
 *
 * AholaX is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * AholaX is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * AholaX; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 */
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