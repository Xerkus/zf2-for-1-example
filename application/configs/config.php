<?php
$config['pluginpaths']['Zf2for1_Resource'] = APPLICATION_PATH . "/../vendor/roave/zf2-for-1/src/Zf2for1/Resource";
$config['resources']['zf2']                = array();

$config['appnamespace']                                        = 'Issues';
$config['resources']['layout']['layoutPath']                   = APPLICATION_PATH . '/layouts';
$config['resources']['layout']['layout']                       = 'layout';
$config['resources']['modules']                                = array();
$config['resources']['view']                                   = array();
$config['bootstrap']['path']                                   = APPLICATION_PATH . '/Bootstrap.php';
$config['bootstrap']['class']                                  = 'Bootstrap';

$config['resources']['frontController']['moduleDirectory']     = APPLICATION_PATH . '/modules';
$config['resources']['frontController']['defaultModule']       = 'default';
$config['resources']['frontController']['throwExceptions']     = false;
$config['resources']['frontController']['prefixDefaultModule'] = true;

$config['resources']['translate']['adapter']                   = 'gettext';
$config['resources']['translate']['content']                   = APPLICATION_PATH . '/languages/en/LC_MESSAGES/default.mo';
$config['resources']['translate']['locale']                    = 'en';

$config['resources']['jquery']['enable']                       = true;
$config['resources']['jquery']['uienable']                     = true;
$config['resources']['jquery']['version']                      = '1.6.2';
$config['resources']['jquery']['uiversion']                    = '1.8.14';
$config['resources']['jquery']['stylesheet']                   = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$config['resources']['jquery']['uiversion'].'/themes/ui-lightness/jquery-ui.css';

$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.development.php';
if (file_exists($file)) {
    include_once $file;
}

return $config;
