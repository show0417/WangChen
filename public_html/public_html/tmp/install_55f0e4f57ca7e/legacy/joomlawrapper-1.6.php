<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.3
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__) . '/joomlawrapper.php';

class MjJoomlaWrapper16 extends MjJoomlaWrapper
{
    public function checkACL()
    {
        return JFactory::getUser()->authorise('core.manage', 'com_mobilejoomla');
    }

    public function getRequestVar($name, $default = null)
    {
        return JRequest::getVar($name, $default);
    }

    public function getRequestWord($name, $default = null)
    {
        return JRequest::getWord($name, $default);
    }

    public function getRequestInt($name, $default = null)
    {
        return JRequest::getInt($name, $default);
    }

    public function raiseWarning($langString, $code = 403)
    {
        JError::raiseWarning($code, JText::_($langString));
    }

    public function loadLanguageFile($extension, $path = JPATH_BASE)
    {
        $lang = JFactory::getLanguage();
        $lang->load($extension, $path, 'en-GB', true);
        $lang->load($extension, $path, null, true);
    }

    public function dbSelectAll($table, $nameColumn = 'name', $valueColumn = 'value')
    {
        $result = array();

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select($db->nameQuote($nameColumn));
        $query->select($db->nameQuote($valueColumn));
        $query->from($table);

        $db->setQuery($query);
        /** @var array $rows */
        $rows = $db->loadAssocList();
        if (count($rows)) {
            foreach ($rows as $row) {
                $result[$row[$nameColumn]] = $row[$valueColumn];
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param string $table
     * @param string $nameColumn
     * @param string $valueColumn
     * @return bool
     */
    public function dbSaveAll($data, $table, $nameColumn = 'name', $valueColumn = 'value')
    {
        $db = JFactory::getDbo();

        $origData = $this->dbSelectAll($table, $nameColumn, $valueColumn);

        foreach ($data as $key => $value) {
            if (isset($origData[$key])) {
                if ($origData[$key] !== $value) {
                    $query = $db->getQuery(true);
                    $query->update($table);
                    $query->set($db->nameQuote($valueColumn) . '=' . $db->Quote($value));
                    $query->where($db->nameQuote($nameColumn) . '=' . $db->Quote($key));
                    $db->setQuery($query);
                    $db->query();
                }
            } else {
                $query = $db->getQuery(true);
                $query->insert($table);
                $query->set($db->nameQuote($nameColumn) . '=' . $db->Quote($key));
                $query->set($db->nameQuote($valueColumn) . '=' . $db->Quote($value));
                $db->setQuery($query);
                $db->query();
            }
        }
        return true;
    }

    public function isMjPluginEnabled()
    {
        jimport('joomla.plugin.helper');
        return JPluginHelper::isEnabled('system', 'mobilejoomla');
    }

    public function enableMjPlugin($enabled)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->update($db->nameQuote('#__extensions'));
        $query->set($db->nameQuote('enabled') . '=' . ($enabled ? '1' : '0'));
        $query->where($db->nameQuote('type') . '=' . $db->Quote('plugin'));
        $query->where($db->nameQuote('folder') . '=' . $db->Quote('system'));
        $query->where($db->nameQuote('element') . '=' . $db->Quote('mobilejoomla'));

        $db->setQuery($query);
        return $db->query();
    }

    public function loadMootools()
    {
        JHtml::_('behavior.framework', true);
    }

    public function changeState($table, $id, $device)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->nameQuote($table));
        $query->where($db->nameQuote('id') . '=' . (int)$id);
        $query->where($db->nameQuote('device') . '=' . $db->Quote($device));

        $db->setQuery($query);
        $unpublished = $db->loadResult();

        $query = $db->getQuery(true);
        if ($unpublished) {
            $query->delete($db->nameQuote($table));
            $query->where($db->nameQuote('id') . '=' . (int)$id);
            $query->where($db->nameQuote('device') . '=' . $db->Quote($device));

            $db->setQuery($query);
            $db->query();

            return true;
        } else {
            $query->insert($db->nameQuote($table));
            $query->columns($db->nameQuote(array('id', 'device')));
            $query->values(implode(',', array((int)$id, $db->Quote($device))));

            $db->setQuery($query);
            $db->query();
            return false;
        }
    }

    public function getConfig($name, $default = null)
    {
        /** @var JRegistry $config */
        $config = JFactory::getConfig();
        return $config->get($name, $default);
    }

    public function setConfig($name, $value)
    {
        /** @var JRegistry $config */
        $config = JFactory::getConfig();
        return $config->set($name, $value);
    }

    public function getDbo()
    {
        // J!1.6 - no dropTable method, so wrapper should be used
        static $database;
        if (!$database) {
            $__dir__ = dirname(__FILE__);
            require_once $__dir__ . '/databasewrapper.php';
            require_once dirname($__dir__) . '/classes/mjquerybuilder.php';
            $database = new MjDatabaseWrapper(JFactory::getDbo());
        }
        return $database;
    }
}