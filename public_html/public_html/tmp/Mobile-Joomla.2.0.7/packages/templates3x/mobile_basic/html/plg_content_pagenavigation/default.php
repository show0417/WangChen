<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		2.0.7
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2015 Mobile Joomla!
 * @date		September 2015
 */
defined('_JEXEC') or die('Restricted access');

$theme = JFactory::getApplication()->getTemplate(true)->params->get('theme_pagination', '');
if($theme)
	$theme = ' data-theme="'.$theme.'"';
?>
<div data-role="controlgroup" data-type="horizontal"<?php echo $theme;?> class="pagenav">
<?php if ($row->prev) : ?>
<a data-role="button" data-inline="true" href="<?php echo $row->prev; ?>" data-direction="reverse" rel="prev"><?php echo JText::_('JGLOBAL_LT') . $pnSpace . JText::_('JPREV'); ?></a>
<?php endif; ?>
<?php if ($row->next) : ?>
<a data-role="button" data-inline="true" href="<?php echo $row->next; ?>" rel="next"><?php echo JText::_('JNEXT') . $pnSpace . JText::_('JGLOBAL_GT'); ?></a>
<?php endif; ?>
</div>