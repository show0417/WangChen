<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version        2.0.4
 * @license        GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright    (C) 2008-2015 Mobile Joomla!
 * @date        September 2015
 */
defined('_JEXEC') or die;

class JElementThemeList extends JElement
{
    var $_name = 'ThemeList';
    var $jqm_label = '';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        $this->jqm_label = parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name);
        return '';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        jimport('joomla.filesystem.folder');

        $supportThemeroller = ($xmlElement->attributes('themeroller') === 'true');
        $demoThemeroller = ($xmlElement->attributes('themeroller') === 'demo');

        $options = array();
        if ($supportThemeroller) {
            $options[] = JHtml::_('select.option', '', JText::_('TPL_MOBILE_JQM__DEFAULT_THEME'), 'value', 'text', false);
        }

        $themesDir = dirname(dirname(__FILE__)) . '/themes';
        $themes = JFolder::exists($themesDir) ? JFolder::folders($themesDir) : array();
        $swatches = array();
        foreach ($themes as $theme) {
            $themefile = dirname(dirname(__FILE__)) . "/themes/$theme/$theme.min.css";
            if (!is_file($themefile)) {
                continue;
            }

            $options[] = JHtml::_('select.option', $theme, $theme, 'value', 'text', false);

            $content = file_get_contents($themefile);
            $max_swatch = 'a';
            if (preg_match_all('#\.ui-body-([a-z])[\s\{,]#', $content, $matches, PREG_PATTERN_ORDER)) {
                $max_swatch = max($matches[1]);
            }
            $swatches[$theme] = ord(strtoupper($max_swatch));
        }
        reset($options);

        $theme_id = $control_name . $name;

        $html = array();
        $html[] = '<{jqmstart}/>';
        if ($supportThemeroller || count($options) > 1) {
            $html[] = '<div class="ui-field-contain">';
            $html[] = $this->jqm_label;
            $html[] = JHtml::_('select.genericlist', $options, $control_name . '[' . $name . ']', ' data-mini="true"', 'value', 'text', $value, $theme_id);
            $html[] = '</div>';
        } else {
            $html[] = '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }

        if ($supportThemeroller) {
            $html[] = '<div class="ui-field-contain">'
                . '<label for="jform_params_themeupload" class="jqmbutton">'
                . '<a href="http://themeroller.jquerymobile.com/?ver=1.4.5" target="_blank" class="ui-btn ui-corner-all ui-mini ui-icon-forward">'
                . JText::_('TPL_MOBILE_JQM__THEME_GENERATE')
                . '</a>'
                . '</label>'
                . '<input type="file" name="themeupload" id="jform_params_themeupload" onchange="ajaxfileupload(this)" accept="application/zip" />'
                . '<iframe width="0" height="0" style="display:none;" name="ajaxUploader-iframe" id="ajaxUploader-iframe"/></iframe>'
                . '</div>';
        } elseif ($demoThemeroller) {
            $html[] = '<div class="ui-field-contain">'
                . '<label for="jform_params_themeupload" class="jqmbutton">'
                . '<a href="http://themeroller.jquerymobile.com/?ver=1.4.5" target="_blank" class="ui-btn ui-corner-all ui-mini ui-icon-forward">'
                . JText::_('TPL_MOBILE_JQM__THEME_GENERATE')
                . '</a>'
                . '</label>'
                . '<a target="_blank" href="http://www.mobilejoomla.com/templates/86-elegance-mobile-joomla-template.html" class="ui-link">'
                . 'Available in Elegance template'
                . '</a>'
                . '</div>';
        }

        $html[] = '<{jqmend}/>';


        $prefix = $control_name . 'theme_';

        $script = 'var jqm_swatches = {' . (($supportThemeroller || $demoThemeroller) ? "'':{}" : '') . '};';

        $swatch_values = array(
            'TPL_MOBILE_JQM__OPTION_DEFAULT',
            'TPL_MOBILE_JQM__OPTION_A',
            'TPL_MOBILE_JQM__OPTION_B'
        );
        $jqm_swatches = array(
            'page' => $swatch_values,
            'header' => $swatch_values,
            'footer' => $swatch_values,
            'moduletitle' => $swatch_values,
            'modulecontent' => $swatch_values,
            'pagination' => $swatch_values,
            'messagetitle' => $swatch_values,
            'messagetext' => $swatch_values,
        );
        if ($supportThemeroller || $demoThemeroller) {
            foreach ($jqm_swatches as $item => $options) {
                foreach ($options as $index => $option) {
                    $jqm_swatches[$item][$index] = "'" . addcslashes(JText::_($option), "'") . "'";
                }
                $script .= "jqm_swatches['']['$item'] = [" . implode(',', $jqm_swatches[$item]) . "];";
            }
        }

        foreach ($swatches as $theme => $max_swatch) {
            $options = array();
            $options[] = JText::_('TPL_MOBILE_JQM__OPTION_DEFAULT');
            if ($max_swatch) {
                for ($c = ord('A'); $c <= $max_swatch; $c++) {
                    $options[] = chr($c);
                }
            }
            foreach ($options as $index => &$option) {
                $option = "'" . addcslashes($option, "'") . "'";
            }
            unset($option);
            $script .= "jqm_swatches['$theme'] = [" . implode(',', $options) . "];";
        }

        $defaultTheme = $xmlElement->attributes('default');
        if ($defaultTheme === null) {
            $defaultTheme = $value;
        }
        $script .= "
var jqm_items = ['page', 'header', 'footer', 'moduletitle', 'modulecontent', 'pagination', 'messagetitle', 'messagetext'];
function onThemeChange(){
	var el=\$('$theme_id'),
		theme = (el && el.options[el.selectedIndex].value) || '$defaultTheme',
		sw = jqm_swatches[theme];
	for(var i=0; i<jqm_items.length; i++){
		var item = jqm_items[i],
			select = \$('$prefix'+item),
			active = select.value=='' ? 0 : select.value.charCodeAt(0)-96;
		if(theme=='')
			sw = jqm_swatches[''][item];
		if(active>=sw.length)
			active = 0;
		select.options.length = 0;
		select.options[0] = new Option(sw[0], '', active=='', false);
		for(var j=1; j<sw.length; j++)
			select.options[j] = new Option(sw[j], String.fromCharCode(96+j), active==j);
		select.selectedIndex = active;
		jqm(select).selectmenu('refresh');
	}
}
jqm(document).on('pagecreate', function(){
	onThemeChange();
	if(\$('$theme_id'))
		\$('$theme_id').addEvent('change', function(){onThemeChange();});
});
		";

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($script);

        return implode($html);
    }
}
