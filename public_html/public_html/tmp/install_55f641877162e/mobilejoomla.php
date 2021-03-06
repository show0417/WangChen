<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.4
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

include_once JPATH_COMPONENT . '/legacy/joomlawrapper.php';
$joomlaWrapper = MjJoomlaWrapper::getInstance();

if (!$joomlaWrapper) {
    echo 'NO WRAPPER FOUND FOR THIS JOOMLA VERSION!';
    return;
}

$joomlaWrapper->loadLanguageFile('com_mobilejoomla', JPATH_ADMINISTRATOR);

//ACL check
if (!$joomlaWrapper->checkACL()) {
    $joomlaWrapper->raiseWarning('JERROR_ALERTNOAUTHOR');
    return;
}

$controllerName = $joomlaWrapper->getRequestWord('controller', 'default');
$action = $joomlaWrapper->getRequestWord('task', 'display');

$filename = JPATH_COMPONENT . '/controllers/' . $controllerName . '.php';
if (!is_file($filename)) {
    $joomlaWrapper->raiseWarning('Controller file is not found');
    return;
}
require_once $filename;

$classname = 'Mj' . $controllerName . 'Controller';
if (!class_exists($classname)) {
    $joomlaWrapper->raiseWarning('Controller class does not exist');
    return;
}


$controller = new $classname($joomlaWrapper);
$controller->name = $controllerName;

/* @todo: move events to wrapper */
JPluginHelper::importPlugin('mobile');
$dispatcher = JDispatcher::getInstance();

if (stripos('2.0.4', 'pro') === false) {
    include_once JPATH_ADMINISTRATOR . '/components/com_mobilejoomla/classes/mjprostub.php';
    new plgMobileMJProStub($dispatcher);
}

$dispatcher->trigger('onMJBeforeDispatch', array($controller, $action));

$controller->execute($action);

