<?php
/**
 * Represents a menu item at the top of the MODX manager.
 *
 * @package modx
 */
class modMenu extends modAccessibleObject {

    /**
     * Overrides xPDOObject::save to cache the menus.
     *
     * {@inheritdoc}
     */
    public function save($cacheFlag = null) {
        $saved = parent::save($cacheFlag);
        if ($saved && empty($this->xpdo->config[xPDO::OPT_SETUP])) {
            $this->rebuildCache();
        }
        return $saved;
    }

    /**
     * Overrides xPDOObject::remove to cache the menus.
     *
     * {@inheritdoc}
     */
    public function remove(array $ancestors = array()) {
        $removed = parent::remove($ancestors);
        if ($removed && empty($this->xpdo->config[xPDO::OPT_SETUP])) {
            $this->rebuildCache();
        }
        return $removed;
    }

    /**
     * Rebuilds the menu map cache.
     *
     * @access public
     * @param integer $start The start menu to build from recursively.
     * @return array An array of modMenu objects, in tree form.
     */
    public function rebuildCache($start = '') {
        $menus = $this->getSubMenus($start);
        $cached = $this->xpdo->cacheManager->set('mgr/menus/'.$this->xpdo->getOption('manager_language',null,$this->xpdo->getOption('cultureKey',null,'en')), $menus, 0, array(
            xPDO::OPT_CACHE_KEY => $this->xpdo->cacheManager->getOption('cache_menu_key', null, 'menu'),
            xPDO::OPT_CACHE_HANDLER => $this->xpdo->cacheManager->getOption('cache_menu_handler', null, $this->xpdo->getOption(xPDO::OPT_CACHE_HANDLER))
        ));
        if ($cached === false) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,'The menu cache could not be written.');
        }

        return $menus;
    }

    /**
     * Gets all submenus from a start menu.
     *
     * @access public
     * @param integer $start The top menu to load from.
     * @return array An array of modMenu objects, in tree form.
     */
    public function getSubMenus($start = '') {
        if (!$this->xpdo->lexicon) {
            $this->xpdo->getService('lexicon','modLexicon');
        }
        $this->xpdo->lexicon->load('menu','topmenu');

        $c = $this->xpdo->newQuery('modMenu');
        $c->leftJoin('modAction','Action');
        $c->select($this->xpdo->getSelectColumns('modMenu', 'modMenu'));
        $c->select($this->xpdo->getSelectColumns('modAction', 'Action', '', array('controller', 'namespace')));
        $c->where(array(
            'modMenu.parent' => $start,
        ));
        $c->sortby($this->xpdo->getSelectColumns('modMenu','modMenu','',array('menuindex')),'ASC');
        $menus = $this->xpdo->getCollection('modMenu',$c);
        if (count($menus) < 1) return array();

        $list = array();
        foreach ($menus as $menu) {
            $ma = $menu->toArray();

            /* if 3rd party menu item, load proper text */
            if ($menu->get('action')) {
                $namespace = $menu->get('namespace');
                if ($namespace != null && $namespace != 'core') {
                    $this->xpdo->lexicon->load($namespace.':default');
                    $ma['text'] = $this->xpdo->lexicon($menu->get('text'));
                } else {
                    $ma['text'] = $this->xpdo->lexicon($menu->get('text'));
                }
            } else {
                $ma['text'] = $this->xpdo->lexicon($menu->get('text'));
            }

            $desc = $menu->get('description');
            $ma['description'] = !empty($desc) ? $this->xpdo->lexicon($desc) : '';
            $ma['children'] = $menu->get('text') != '' ? $this->getSubMenus($menu->get('text')) : array();

            if ($menu->get('controller')) {
                $ma['controller'] = $menu->get('controller');
            } else {
                $ma['controller'] = '';
            }
            $list[] = $ma;
        }
        unset($menu,$desc,$namespace,$ma);
        return $list;
    }
}