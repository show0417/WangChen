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

require_once JPATH_COMPONENT . '/classes/mjcontroller.php';

class MjExtensionsController extends MjController
{
    public function getImage($status)
    {
        /* @todo: move to view class */
        return $status
            ? '<img src="components/com_mobilejoomla/assets/images/publ-16.png" width="16" height="16" />'
            : '<img src="components/com_mobilejoomla/assets/images/unpubl-16.png" width="16" height="16" />';
    }

    public function set_module_state()
    {
        /* @todo move to model */
        if (!headers_sent()) {
            header('Content-Type: text/html');
            header('Cache-Control: private');
        }

        $id = $this->joomlaWrapper->getRequestInt('id');
        $device = $this->joomlaWrapper->getRequestWord('device');
        $published = $this->joomlaWrapper->changeState('#__mj_modules', $id, $device);
        echo $this->getImage($published);

        $app = JFactory::getApplication();
        $app->close();
    }

    public function set_plugin_state()
    {
        if (!headers_sent()) {
            header('Content-Type: text/html');
            header('Cache-Control: private');
        }

        $id = $this->joomlaWrapper->getRequestInt('id');
        $device = $this->joomlaWrapper->getRequestWord('device');
        $published = $this->joomlaWrapper->changeState('#__mj_plugins', $id, $device);
        echo $this->getImage($published);

        $app = JFactory::getApplication();
        $app->close();
    }
}