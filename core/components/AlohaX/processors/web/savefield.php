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
$saved = false;

if($resource = $modx->getObject('modResource', $scriptProperties['id'])){
	foreach($scriptProperties as $fieldname => $value){
		//this is a temporary measure to avoid whitespace added by aloha
		$scriptProperties[$fieldname] = trim($value);
	}
	if(array_key_exists($scriptProperties['field'], $resource->toArray())){
		$resource->set($scriptProperties['field'], $scriptProperties['content']);
	} else {
		//its a TV, maybe. if not it will go unsaved.
		$tvs = $resource->getMany('TemplateVars');
		foreach($tvs as $tvId => $tv){
			if($tv->get('name') == $scriptProperties['field']){
				//save the tv value
				$tv->setValue($resource->get('id'), $scriptProperties['content']);
				$saved = $tv->save();	
			}
		}
	}
	$saved = $resource->save();
}

if($saved){
	$modx->cacheManager->clearCache();
	return $modx->error->success("Successfully saved " . $scriptProperties['field'] . " field.");
}

return $modx->error->failure("Error! The " . $scriptProperties['field'] . " wasn't saved properly.");