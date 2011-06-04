<?php
/*
 * MODX Revolution
 *
 * Copyright 2006-2011 by MODX, LLC.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Common classes for the MODX installation and provisioning services.
 *
 * @package setup
 */
/**
 * Provides common functionality and data for installation and provisioning.
 *
 * @package setup
 */
class modInstall {
    const MODE_NEW = 0;
    const MODE_UPGRADE_REVO = 1;
    const MODE_UPGRADE_EVO = 2;
    const MODE_UPGRADE_REVO_ADVANCED = 3;

    public $xpdo = null;
    public $options = array ();
    public $config = array ();
    public $action = '';
    public $lexicon = null;
    public $finished = false;

    /**
     * The constructor for the modInstall object.
     *
     * @constructor
     * @param array $options An array of configuration options.
     */
    function __construct(array $options = array()) {
        if (isset ($_REQUEST['action'])) {
            $this->action = $_REQUEST['action'];
        }
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * Loads the request handler for the setup.
     * @return boolean True if successful.
     */
    public function loadRequestHandler($class = 'modInstallRequest') {
        $path = dirname(__FILE__).'/'.strtolower($class).'.class.php';
        $included = @include $path;
        if ($included) {
            $this->request = new $class($this);
        } else {
            $this->_fatalError($this->lexicon('request_handler_err_nf',array('path' => $path)));
        }
        return $included;
    }

    /**
     * Load settings class
     *
     * @access public
     * @param string $class The settings class to load.
     * @return boolean True if successful.
     */
    public function loadSettings($class = 'modInstallSettings') {
        $path = dirname(__FILE__).'/'.strtolower($class).'.class.php';
        $included = @include_once $path;
        if ($included) {
            $this->settings = new $class($this);
        } else {
            $this->_fatalError($this->lexicon('settings_handler_err_nf',array('path' => $path)));
        }
        return $included;
    }

    /**
     * Loads the lexicon class for the install process.
     *
     * @param string $class The class name of the lexicon class to use.
     * @return boolean True if successful.
     */
    public function loadLexicon($class = 'modInstallLexicon') {
        $path = dirname(__FILE__).'/'.strtolower($class).'.class.php';
        $included = @include $path;
        $this->lexicon = new modInstallLexicon($this);
        return $included;
    }

    /**
     * Shortcut method for modInstallLexicon::get. {@see modInstallLexicon::get}
     */
    public function lexicon($key,array $placeholders = array()) {
        return $this->lexicon->get($key,$placeholders);
    }

    /**
     * Get the existing or create a new configuration.
     *
     * @param integer $mode The install mode.
     * @param array $config An array of config attributes.
     * @return array A copy of the config attributes array.
     */
    public function getConfig($mode = 0, $config = array ()) {
        global $database_dsn, $database_type, $database_server, $dbase, $database_user,
                $database_password, $database_connection_charset, $table_prefix, $config_options;
        $database_connection_charset = 'utf8';
        if (!is_array($config)) {
            $config = array ();
        }

        /* get http host */
        $https_port = isset ($_POST['httpsport']) ? $_POST['httpsport'] : '443';
        $isSecureRequest = ((isset ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || $_SERVER['SERVER_PORT'] == $https_port);
        $http_host= $_SERVER['HTTP_HOST'];
        if ($_SERVER['SERVER_PORT'] != 80) {
            $http_host= str_replace(':' . $_SERVER['SERVER_PORT'], '', $http_host); /* remove port from HTTP_HOST */
        }
        $http_host .= ($_SERVER['SERVER_PORT'] == 80 || $isSecureRequest) ? '' : ':' . $_SERVER['SERVER_PORT'];

        switch ($mode) {
            case modInstall::MODE_UPGRADE_EVO :
                $included = @ include MODX_INSTALL_PATH . 'manager/includes/config.inc.php';
                if ($included && isset ($dbase))
                    break;

            case modInstall::MODE_UPGRADE_REVO :
            case modInstall::MODE_UPGRADE_REVO_ADVANCED :
                $included = @ include MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
                if ($included && isset ($dbase)) {
                    $config['mgr_path'] = MODX_MANAGER_PATH;
                    $config['connectors_path'] = MODX_CONNECTORS_PATH;
                    $config['web_path'] = MODX_BASE_PATH;
                    $config['context_mgr_path'] = MODX_MANAGER_PATH;
                    $config['context_connectors_path'] = MODX_CONNECTORS_PATH;
                    $config['context_web_path'] = MODX_BASE_PATH;

                    $config['mgr_url'] = MODX_MANAGER_URL;
                    $config['connectors_url'] = MODX_CONNECTORS_URL;
                    $config['web_url'] = MODX_BASE_URL;
                    $config['context_mgr_url'] = MODX_MANAGER_URL;
                    $config['context_connectors_url'] = MODX_CONNECTORS_URL;
                    $config['context_web_url'] = MODX_BASE_URL;

                    $config['core_path'] = MODX_CORE_PATH;
                    $config['processors_path'] = MODX_CORE_PATH.'model/modx/processors/';
                    $config['assets_path'] = MODX_ASSETS_PATH;
                    $config['assets_url'] = MODX_ASSETS_URL;

                    $config_options = !empty($config_options) ? $config_options : array();
                    break;
                }

            default :
                $included = false;
                $database_type = isset ($_POST['databasetype']) ? $_POST['databasetype'] : 'mysql';
                $database_server = isset ($_POST['databasehost']) ? $_POST['databasehost'] : 'localhost';
                $database_user = isset ($_POST['databaseloginname']) ? $_POST['databaseloginname'] : '';
                $database_password = isset ($_POST['databaseloginpassword']) ? $_POST['databaseloginpassword'] : '';
                $dbase = isset ($_POST['database_name']) ? $_POST['database_name'] : 'modx';
                $table_prefix = isset ($_POST['tableprefix']) ? $_POST['tableprefix'] : 'modx_';
                $https_port = isset ($_POST['httpsport']) ? $_POST['httpsport'] : '443';
                $cache_disabled = isset ($_POST['cache_disabled']) ? $_POST['cache_disabled'] : 'false';
                $site_sessionname = 'SN' . uniqid('');
                $config_options = array();
                break;
        }
        $config = array_merge($config,array(
            'database_type' => $database_type,
            'database_server' => $database_server,
            'dbase' => trim($dbase, '`[]'),
            'database_user' => $database_user,
            'database_password' => $database_password,
            'database_connection_charset' => $database_connection_charset,
            'database_charset' => $database_connection_charset,
            'table_prefix' => $table_prefix,
            'https_port' => isset ($https_port) ? $https_port : '443',
            'http_host' => defined('MODX_HTTP_HOST') ? MODX_HTTP_HOST : $http_host,
            'site_sessionname' => isset ($site_sessionname) ? $site_sessionname : 'SN' . uniqid(''),
            'cache_disabled' => isset ($cache_disabled) && $cache_disabled ? 'true' : 'false',
            'inplace' => isset ($_POST['inplace']) ? 1 : 0,
            'unpacked' => isset ($_POST['unpacked']) ? 1 : 0,
            'config_options' => $config_options,
        ));
        $this->config = array_merge($this->config, $config);
        switch ($this->config['database_type']) {
            case 'sqlsrv':
                $database_dsn = $this->config['database_dsn'] = "{$this->config['database_type']}:server={$this->config['database_server']};database={$this->config['dbase']}";
                break;
            case 'mysql':
                $database_dsn = $this->config['database_dsn'] = "{$this->config['database_type']}:host={$this->config['database_server']};dbname={$this->config['dbase']};charset={$this->config['database_connection_charset']}";
                break;
            default:
                break;
        }
        return $this->config;
    }

    /**
     * Get an xPDO connection to the database.
     *
     * @return xPDO A copy of the xpdo object.
     */
    public function getConnection($mode = modInstall::MODE_NEW) {
        if ($mode === modInstall::MODE_UPGRADE_REVO) {
            $errors = array ();
            $this->xpdo = $this->_modx($errors);
        } else if (!is_object($this->xpdo)) {
            $options = array();
            if ($this->settings->get('new_folder_permissions')) $options['new_folder_permissions'] = $this->settings->get('new_folder_permissions');
            if ($this->settings->get('new_file_permissions')) $options['new_file_permissions'] = $this->settings->get('new_file_permissions');
            $this->xpdo = $this->_connect(
                $this->settings->get('database_dsn')
                ,$this->settings->get('database_user')
                ,$this->settings->get('database_password')
                ,$this->settings->get('table_prefix')
                ,$options
             );

            if (!($this->xpdo instanceof xPDO)) { return $this->xpdo; }

            $this->xpdo->setOption('cache_path',MODX_CORE_PATH . 'cache/');

            if ($mode === modInstall::MODE_UPGRADE_REVO_ADVANCED) {
                if ($this->xpdo->connect()) {
                    $errors = array ();
                    $this->xpdo = $this->_modx($errors);
                } else {
                    return $this->lexicon('db_err_connect_upgrade');
                }
            }
        }
        if (is_object($this->xpdo) && $this->xpdo instanceof xPDO) {
            $this->xpdo->setLogTarget(array(
                'target' => 'FILE',
                'options' => array(
                    'filename' => 'install.' . MODX_CONFIG_KEY . '.' . strftime('%Y-%m-%dT%H.%M.%S')
                )
            ));
            $this->xpdo->setLogLevel(xPDO::LOG_LEVEL_ERROR);
            $this->xpdo->setPackage('modx', MODX_CORE_PATH . 'model/', $this->settings->get('table_prefix'));
        }
        return $this->xpdo;
    }

    /**
     * Load distribution-specific test handlers
     */
    public function loadTestHandler($class = 'modInstallTest') {
        $path = dirname(__FILE__).'/'.strtolower($class).'.class.php';
        $included = @include $path;
        if ($included) {
            $this->lexicon->load('test');

            $class = $class.ucfirst(trim(MODX_SETUP_KEY, '@'));
            $versionPath = dirname(__FILE__).'/checks/'.strtolower($class).'.class.php';
            $included = @include $versionPath;
            if (!$included) {
                $this->_fatalError($this->lexicon('test_version_class_nf',array('path' => $versionPath)));
            }
            $this->test = new $class($this);
            return $this->test;
        } else {
            $this->_fatalError($this->lexicon('test_class_nf',array('path' => $path)));
        }
    }

    /**
     * Perform a series of pre-installation tests.
     *
     * @param integer $mode The install mode.
     * @param string $test_class The class to run tests with
     * @return array An array of result messages collected during the process.
     */
    public function test($mode = modInstall::MODE_NEW,$test_class = 'modInstallTest') {
        $test = $this->loadTestHandler($test_class);
        $results = $this->test->run($mode);
        return $results;
    }

    /**
     * Load version-specific installer.
     *
     * @access public
     * @param string $class The class to load.
     */
    public function loadVersionInstaller($class = 'modInstallVersion') {
        $path = dirname(__FILE__).'/'.strtolower($class).'.class.php';
        $included = @include $path;
        if ($included) {
            $this->versioner = new $class($this);
            return $this->versioner;
        } else {
            $this->_fatalError($this->lexicon('versioner_err_nf',array('path' => $path)));
        }
    }

    /**
     * Execute the installation process.
     *
     * @param integer $mode The install mode.
     * @return array An array of result messages collected during execution.
     */
    public function execute($mode) {
        $results = array ();
        /* set the time limit infinite in case it takes a bit
         * TODO: fix this by allowing resume when it takes a long time
         */
        @ set_time_limit(0);
        @ ini_set('max_execution_time', 240);
        @ ini_set('memory_limit','128M');

        /* write config file */
        $this->writeConfig($results);

        /* get connection */
        $this->getConnection($mode);

        /* run appropriate database routines */
        switch ($mode) {
            /* TODO: MODX Evolution to Revolution migration */
            case modInstall::MODE_UPGRADE_EVO :
                $results = include MODX_SETUP_PATH . 'includes/tables_migrate.php';
                break;
                /* revo-alpha+ upgrades */
            case modInstall::MODE_UPGRADE_REVO :
            case modInstall::MODE_UPGRADE_REVO_ADVANCED :
                $this->loadVersionInstaller();
                $results = $this->versioner->install();
                break;
                /* new install, create tables */
            default :
                $results = include MODX_SETUP_PATH . 'includes/tables_create.php';
                break;
        }

        if ($this->xpdo) {
            /* add required core data */
            $this->xpdo->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);

            $packageDirectory = MODX_CORE_PATH . 'packages/';
            $packageState = $this->settings->get('unpacked') == 1 ? xPDOTransport::STATE_UNPACKED : xPDOTransport::STATE_PACKED;
            $package = xPDOTransport :: retrieve($this->xpdo, $packageDirectory . 'core.transport.zip', $packageDirectory, $packageState);
            if (!is_object($package) || !($package instanceof xPDOTransport)) {
                $results[] = array (
                    'class' => 'failed',
                    'msg' => '<p class="notok">'.$this->lexicon('package_execute_err_retrieve',array('path' => $this->settings->get('core_path'))).'</p>'
                );
                return $results;
            }

            if (!defined('MODX_BASE_PATH'))
                define('MODX_BASE_PATH', $this->settings->get('context_web_path'));
            if (!defined('MODX_ASSETS_PATH'))
                define('MODX_ASSETS_PATH', $this->settings->get('context_assets_path'));
            if (!defined('MODX_MANAGER_PATH'))
                define('MODX_MANAGER_PATH', $this->settings->get('context_mgr_path'));
            if (!defined('MODX_CONNECTORS_PATH'))
                define('MODX_CONNECTORS_PATH', $this->settings->get('context_connectors_path'));

            $package->install(array (
                xPDOTransport::RESOLVE_FILES => ($this->settings->get('inplace') == 0 ? 1 : 0)
                ,xPDOTransport::INSTALL_FILES => ($this->settings->get('inplace') == 0 ? 1 : 0)
                , xPDOTransport::PREEXISTING_MODE => xPDOTransport::REMOVE_PREEXISTING
            ));

            /* set default workspace path */
            $workspace = $this->xpdo->getObject('modWorkspace', array (
                'active' => 1
            ));
            if ($workspace) {
                $path = $workspace->get('path');
                if (!empty($path)) {
                    $path = trim($path);
                }
                if (empty ($path) || !file_exists($path)) {
                    $workspace->set('path', MODX_CORE_PATH);
                    if (!$workspace->save()) {
                        $results[] = array (
                            'class' => 'error',
                            'msg' => '<p class="notok">'.$this->lexicon('workspace_err_path').'</p>'
                        );
                    } else {
                        $results[] = array (
                            'class' => 'success',
                            'msg' => '<p class="ok">'.$this->lexicon('workspace_path_updated').'</p>'
                        );
                    }
                }
            } else {
                $results[] = array (
                    'class' => 'error',
                    'msg' => '<p class="notok">'.$this->lexicon('workspace_err_nf').'</p>'
                );
            }
            unset($workspace);

            $modx =& $this->xpdo;

            /* if new install */
            if ($mode == modInstall::MODE_NEW) {
                include MODX_SETUP_PATH.'includes/new.install.php';

            /* if upgrade */
            } else {
                include MODX_SETUP_PATH.'includes/upgrade.install.php';
            }

            /* empty sessions table to prevent old permissions from loading */
            $tableName = $this->xpdo->getTableName('modSession');
            $this->xpdo->exec($this->driver->truncate($tableName));

            /* clear cache */
            $this->xpdo->cacheManager->deleteTree(MODX_CORE_PATH.'cache/',array(
                'skipDirs' => false,
                'extensions' => array(
                    '.cache.php',
                    '.tpl.php',
                ),
            ));

            $this->settings->store(array(
                'finished' => true,
            ));
        }

        return $results;
    }

    /**
     * Verify that the modX class can be initialized.
     *
     * @param integer $mode Indicates the installation mode.
     * @return array An array of error messages collected during the process.
     */
    public function verify() {
        $errors = array ();
        $modx = $this->_modx($errors);
        if (is_object($modx) && $modx instanceof modX) {
            if ($modx->getCacheManager()) {
                $modx->cacheManager->refresh();
            }
        }
        return $errors;
    }

    /**
     * Cleans up after install.
     *
     * TODO: implement this function to cleanup any temporary files
     * @param array $options
     */
    public function cleanup(array $options = array ()) {
        $errors = array();
        $modx = $this->_modx($errors);
        if (empty($modx) || !($modx instanceof modX)) {
            $errors['modx_class'] = $this->lexicon('modx_err_instantiate');
            return $errors;
        }

        /* create the directories for Package Management */
        $cacheManager = $modx->getCacheManager();
        $directoryOptions = array(
            'new_folder_permissions' => $modx->getOption('new_folder_permissions',null,0775),
        );

        /* create assets/ */
        $assetsPath = $modx->getOption('base_path').'assets/';
        if (!is_dir($assetsPath)) {
            $cacheManager->writeTree($assetsPath,$directoryOptions);
        }
        if (!is_dir($assetsPath) || !$this->is_writable2($assetsPath)) {
            $errors['assets_not_created'] = str_replace('[[+path]]',$assetsPath,$this->lexicon('setup_err_assets'));
        }
        unset($assetsPath);

        /* create assets/components/ */
        $assetsCompPath = $modx->getOption('base_path').'assets/components/';
        if (!is_dir($assetsCompPath)) {
            $cacheManager->writeTree($assetsCompPath,$directoryOptions);
        }
        if (!is_dir($assetsCompPath) || !$this->is_writable2($assetsCompPath)) {
            $errors['assets_comp_not_created'] = str_replace('[[+path]]',$assetsCompPath,$this->lexicon('setup_err_assets_comp'));
        }
        unset($assetsCompPath);

        /* create core/components/ */
        $coreCompPath = $modx->getOption('core_path').'components/';
        if (!is_dir($coreCompPath)) {
            $cacheManager->writeTree($coreCompPath,$directoryOptions);
        }
        if (!is_dir($coreCompPath) || !$this->is_writable2($coreCompPath)) {
            $errors['core_comp_not_created'] = str_replace('[[+path]]',$coreCompPath,$this->lexicon('setup_err_core_comp'));
        }
        unset($coreCompPath);

        return $errors;
    }

    /**
     * Removes the setup directory
     *
     * @access publics
     */
    public function removeSetupDirectory(array $options = array()) {
        $errors = array();

        $modx = $this->_modx($errors);
        if ($modx) {
            $cacheManager = $modx->getCacheManager();
            if ($cacheManager) {
                $setupPath = $modx->getOption('base_path').'setup/';
                if (!$cacheManager->deleteTree($setupPath,true,false,false)) {
                    $modx->log(modX::LOG_LEVEL_ERROR,$this->lexicon('setup_err_remove'));
                }
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR,$this->lexicon('cache_manager_err'));
            }
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR,$this->lexicon('modx_object_err'));
        }
        return $errors;
    }

    /**
     * Writes the config file.
     *
     * @param array $results An array of result messages.
     * @return boolean Returns true if successful; false otherwise.
     */
    public function writeConfig(array &$results) {
        $written = false;
        $configTpl = MODX_CORE_PATH . 'docs/config.inc.tpl';
        $configFile = MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';

        $settings = $this->settings->fetch();
        $settings['last_install_time'] = time();
        $settings['site_id'] = uniqid('modx',true);

        /* make UUID if not set */
        if (empty($settings['uuid'])) {
            $settings['uuid'] = $this->generateUUID();
        }

        if (file_exists($configTpl)) {
            if ($tplHandle = @ fopen($configTpl, 'rb')) {
                $content = @ fread($tplHandle, filesize($configTpl));
                @ fclose($tplHandle);
                if ($content) {
                    $replace = array ();
                    while (list ($key, $value) = each($settings)) {
                        if (is_scalar($value)) {
                            $replace['{' . $key . '}'] = "{$value}";
                        } elseif (is_array($value)) {
                            $replace['{' . $key . '}'] = var_export($value, true);
                        }
                    }
                    $content = str_replace(array_keys($replace), array_values($replace), $content);
                    if ($configHandle = @ fopen($configFile, 'wb')) {
                        $written = @ fwrite($configHandle, $content);
                        @ fclose($configHandle);
                    }
                }
            }
        }
        $perms = $this->settings->get('new_file_permissions', sprintf("%04o", 0666 & (0666 - umask())));
        if (is_string($perms)) $perms = octdec($perms);
        $chmodSuccess = @ chmod($configFile, $perms);
        if (!is_array($results)) {
            $results = array ();
        }
        if ($written) {
            $results[] = array (
                'class' => 'success',
                'msg' => '<p class="ok">'.$this->lexicon('config_file_written').'</p>'
            );
        } else {
            $results[] = array (
                'class' => 'failed',
                'msg' => '<p class="notok">'.$this->lexicon('config_file_err_w').'</p>'
            );
        }
        if ($chmodSuccess) {
            $results[] = array (
                'class' => 'success',
                'msg' => '<p class="ok">'.$this->lexicon('config_file_perms_set').'</p>'
            );
        } else {
            $results[] = array (
                'class' => 'warning',
                'msg' => '<p>'.$this->lexicon('config_file_perms_notset').'</p>'
            );
        }
        return $results;
    }

    /**
     * Generates a random universal unique ID for identifying modx installs
     *
     * @return string A universally unique ID
     */
    public function generateUUID() {
        srand(intval(microtime(true) * 1000));
        $b = md5(uniqid(rand(),true),true);
        $b[6] = chr((ord($b[6]) & 0x0F) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3F) | 0x80);
        return implode('-',unpack('H8a/H4b/H4c/H4d/H12e',$b));
    }

    /**
     * Installs a transport package.
     *
     * @param string The package signature.
     * @param array $attributes An array of installation attributes.
     * @return array An array of error messages collected during the process.
     */
    public function installPackage($pkg, array $attributes = array ()) {
        $errors = array ();

        /* instantiate the modX class */
        if (@ require_once (MODX_CORE_PATH . 'model/modx/modx.class.php')) {
            $modx = new modX(MODX_CORE_PATH . 'config/');
            if (!is_object($modx) || !($modx instanceof modX)) {
                $errors[] = '<p>'.$this->lexicon('modx_err_instantiate').'</p>';
            } else {
                /* try to initialize the mgr context */
                $modx->initialize('mgr');
                if (!$modx->isInitialized()) {
                    $errors[] = '<p>'.$this->lexicon('modx_err_instantiate_mgr').'</p>';
                } else {
                    $loaded = $modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
                    if (!$loaded)
                        $errors[] = '<p>'.$this->lexicon('transport_class_err_load').'</p>';

                    $packageDirectory = MODX_CORE_PATH . 'packages/';
                    $packageState = (isset ($attributes[xPDOTransport::PACKAGE_STATE]) ? $attributes[xPDOTransport::PACKAGE_STATE] : xPDOTransport::STATE_PACKED);
                    $package = xPDOTransport :: retrieve($modx, $packageDirectory . $pkg . '.transport.zip', $packageDirectory, $packageState);
                    if ($package) {
                        if (!$package->install($attributes)) {
                            $errors[] = '<p>'.$this->lexicon('package_err_install',array('package' => $pkg)).'</p>';
                        } else {
                            $modx->log(xPDO::LOG_LEVEL_INFO,$this->lexicon('package_installed',array('package' => $pkg)));
                        }
                    } else {
                        $errors[] = '<p>'.$this->lexicon('package_err_nf',array('package' => $pkg)).'</p>';
                    }
                }
            }
        } else {
            $errors[] = '<p>'.$this->lexicon('modx_class_err_nf').'</p>';
        }

        return $errors;
    }

    /**
     * Gets the manager login URL.
     *
     * @return string The URL of the installed manager context.
     */
    public function getManagerLoginUrl() {
        $url = '';

        /* instantiate the modX class */
        if (@ require_once (MODX_CORE_PATH . 'model/modx/modx.class.php')) {
            $modx = new modX(MODX_CORE_PATH . 'config/');
            if (is_object($modx) && $modx instanceof modX) {
                /* try to initialize the mgr context */
                $modx->initialize('mgr');
                $url = MODX_URL_SCHEME.$modx->getOption('http_host').$modx->getOption('manager_url');
            }
        }
        return $url;
    }

    /**
     * Determines the possible install modes.
     *
     * @access public
     * @return integer One of three possible mode indicators:<ul>
     * <li>0 = new install only</li>
     * <li>1 = new OR upgrade from older versions of MODX Revolution</li>
     * <li>2 = new OR upgrade from MODX Evolution</li>
     * </ul>
     */
    public function getInstallMode() {
        $mode = modInstall::MODE_NEW;
        if (isset ($_POST['installmode'])) {
            $mode = intval($_POST['installmode']);
        } else {
            global $dbase;
            if (file_exists(MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php')) {
                /* Include the file so we can test its validity */
                $included = @ include (MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php');
                $mode = ($included && isset ($dbase)) ? modInstall::MODE_UPGRADE_REVO : modInstall::MODE_NEW;
            }
            if (!$mode && file_exists(MODX_INSTALL_PATH . 'manager/includes/config.inc.php')) {
                $included = @ include (MODX_INSTALL_PATH . 'manager/includes/config.inc.php');
                $mode = ($included && isset ($dbase)) ? modInstall::MODE_UPGRADE_EVO : modInstall::MODE_NEW;
            }
        }
        return $mode;
    }

    /**
     * Creates the database connection for the installation process.
     *
     * @access private
     * @return xPDO The xPDO instance to be used by the installation.
     */
    public function _connect($dsn, $user = '', $password = '', $prefix = '', array $options = array()) {
        if (include_once (MODX_CORE_PATH . 'xpdo/xpdo.class.php')) {
            $this->xpdo = new xPDO($dsn, $user, $password, array_merge(array(
                    xPDO::OPT_CACHE_PATH => MODX_CORE_PATH . 'cache/',
                    xPDO::OPT_TABLE_PREFIX => $prefix,
                    xPDO::OPT_LOADER_CLASSES => array('modAccessibleObject'),
                    xPDO::OPT_SETUP => true,
                ), $options),
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT)
            );
            $this->xpdo->setLogTarget(array(
                'target' => 'FILE',
                'options' => array(
                    'filename' => 'install.' . MODX_CONFIG_KEY . '.' . strftime('%Y%m%dT%H%M%S') . '.log'
                )
            ));
            $this->xpdo->setLogLevel(xPDO::LOG_LEVEL_ERROR);
            return $this->xpdo;
        } else {
            return $this->lexicon('xpdo_err_nf', array('path' => MODX_CORE_PATH.'xpdo/xpdo.class.php'));
        }
    }

    /**
     * Instantiate an existing modX configuration.
     *
     * @param array &$errors An array in which error messages are collected.
     * @return modX|null The modX instance, or null if it could not be instantiated.
     */
    private function _modx(array & $errors) {
        $modx = null;

        /* to validate installation, instantiate the modX class and run a few tests */
        if (include_once (MODX_CORE_PATH . 'model/modx/modx.class.php')) {
            $modx = new modX(MODX_CORE_PATH . 'config/', array(
                xPDO::OPT_SETUP => true,
            ));
            if (!is_object($modx) || !($modx instanceof modX)) {
                $errors[] = '<p>'.$this->lexicon('modx_err_instantiate').'</p>';
            } else {
                $modx->setLogTarget(array(
                    'target' => 'FILE',
                    'options' => array(
                        'filename' => 'install.' . MODX_CONFIG_KEY . '.' . strftime('%Y%m%dT%H%M%S') . '.log'
                    )
                ));

                /* try to initialize the mgr context */
                $modx->initialize('mgr');
                if (!$modx->isInitialized()) {
                    $errors[] = '<p>'.$this->lexicon('modx_err_instantiate_mgr').'</p>';
                }
            }
        } else {
            $errors[] = '<p>'.$this->lexicon('modx_class_err_nf').'</p>';
        }

        return $modx;
    }

    /**
     * Finds the core directory, if possible. If core cannot be found, loads the
     * findcore controller.
     *
     * @return Returns true if core directory is found.
     */
    public function findCore() {
        $exists = false;
        if (defined('MODX_CORE_PATH') && file_exists(MODX_CORE_PATH) && is_dir(MODX_CORE_PATH)) {
            if (file_exists(MODX_CORE_PATH . 'xpdo/xpdo.class.php') && file_exists(MODX_CORE_PATH . 'model/modx/modx.class.php')) {
                $exists = true;
            }
        }
        if (!$exists) {
            include(MODX_SETUP_PATH . 'templates/findcore.php');
            die();
        }
        return $exists;
    }

    /**
     * Does all the pre-load checks, before setup loads.
     *
     * @access public
     */
    public function doPreloadChecks() {
        $this->lexicon->load('preload');
        $errors= array();

        if (!extension_loaded('pdo')) {
            $errors[] = $this->lexicon('preload_err_pdo');
        }
        if (!file_exists(MODX_CORE_PATH) || !is_dir(MODX_CORE_PATH)) {
            $errors[] = $this->lexicon('preload_err_core_path');
        }
        if (!file_exists(MODX_CORE_PATH . 'cache/') || !is_dir(MODX_CORE_PATH . 'cache/') || !$this->is_writable2(MODX_CORE_PATH . 'cache/')) {
            $errors[] = $this->lexicon('preload_err_cache',array('path' => MODX_CORE_PATH));
        }

        if (!empty($errors)) {
            $this->_fatalError($errors);
        }
    }

    /**
     * Outputs a fatal error message and then dies.
     *
     * @access private
     * @param string/array A string or array of errors
     */
    private function _fatalError($errors) {
        $output = '<html><head><title></title></head><body><h1>'.$this->lexicon('fatal_error').'</h1><ul>';
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $output .= '<li>'.$error.'</li>';
            }
        } else {
            $output .= '<li>'.$errors.'</li>';
        }
        $output .= '</ul></body></html>';
        die($output);
    }

    /**
     * Custom is_writable function to test on problematic servers
     *
     * @param string $path
     * @return boolean True if write was successful
     */
    public function is_writable2($path) {
        $written = false;
        if (!is_string($path)) return false;

        /* if is file get parent dir */
        if (is_file($path)) { $path = dirname($path) . '/'; }

        /* ensure / at end, translate \ to / for windows */
        if (substr($path,strlen($path)-1) != '/') { $path .= '/'; }
        $path = strtr($path,'\\','/');

        /* get test file */
        $filePath = $path.uniqid().'.cache.php';

        /* attempt to create test file */
        $fp = @fopen($filePath,'w');
        if ($fp === false || !file_exists($filePath)) return false;

        /* attempt to write to test file */
        $written = @fwrite($fp,'<?php echo "test";');
        if (!$written) { /* if fails try to delete it */
            @fclose($fp);
            @unlink($filePath);
            return false;
        }

        /* attempt to delete test file */
        @fclose($fp);
        $written = @unlink($filePath);

        return $written;
    }

    /**
     * Loads the correct database driver for this environment.
     *
     * @return boolean True if successful.
     */
    public function loadDriver() {
        $this->loadSettings();
        $path = dirname(__FILE__).'/drivers/';

        /* db specific driver */
        $class = 'modInstallDriver_'.strtolower($this->settings->get('database_type','mysql'));
        $driverPath = $path.strtolower($class.'.class.php');
        $included = @include_once $driverPath;
        if ($included) {
            $this->driver = new $class($this);
        } else {
            $this->_fatalError($this->lexicon('driver_class_err_nf',array('path' => $driverPath)));
        }
        return $included;
    }
}
