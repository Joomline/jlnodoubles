<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * @package mod_jlnodubles
 * @author Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 1.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */

jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once JPATH_ROOT . '/plugins/system/jlnodoubles/helpers/helper.php';

class JFormFieldComponents extends JFormField
{
    private $components;

    protected function getInput()
    {
        $db = JFactory::getDBO();
        $doc = JFactory::getDocument();
        $db->setQuery('SELECT element FROM #__extensions WHERE type="component" ORDER BY name');
        $components = $db->loadColumn();

        jimport('joomla.filesystem.folder');
        $folders = JFolder::folders(JPATH_ROOT . '/components', 'com_(.*)');

        if (!$this->value) {
            $this->value = array(
                0 => array(
                    'var_name' => array(
                        'task',
                        'format',
                        'no_html',
                        'tmpl',
                    ),
                    'var_value' => array(
                        'save, edit, add, delete, apply',
                        'nohtml',
                        '1',
                        'component',
                    )
                ),
                'com_content' => array(
                    'checkbox' => 'on',
                    'var_name' => array(''),
                    'var_value' => array(''),
                )
            );
        }

        $this->components = JFolder::files(JPATH_ROOT.'/plugins/system/jlnodoubles/helpers');

        $plugin = JPluginHelper::getPlugin('system', 'jlnodoubles');
        $pluginParams = new JRegistry($plugin->params);
        $allow = (JLNodoublesHelper::allow($pluginParams->get('key', ''))) ? true : false;

        $all = JText::_('PLG_JLNODUBLES_ALL_COMPONENTS');
        array_unshift($components, $all);
        ?>
        <div id="sh_component_wrapper">
            <?php foreach ($components as $component) {
                if (in_array($component, $folders) || $component == $all) {
                    $componentName = $component;
                    if ($component == $all) $component = 0;
                    $checked = (isset($this->value[$component]["checkbox"])) ? ' checked="checked" ' : '';
                    $unchecked_class = ($checked || !$component) ? '' : ' unchecked ';
                    $disabled = (!$allow && $componentName != 'com_content' && $componentName != 'com_tags'
                        && in_array($componentName.'.php', $this->components)) ? ' disabled="disabled"' : '';
                    ?>
                    <div class="sh_component_inner <?php echo $unchecked_class ?>"
                         id="sh_component_<?php echo $component ?>">
                        <div class="sh_component_name">
                            <?php if ($component) : ?>
                                <input type="checkbox"
                                       onclick="shnodoubles.com_checkbox(this, '<?php echo $component ?>')"
                                       class="shnodoubles_com_checkbox"
                                       name="<?php echo $this->name . '[' . $component . '][checkbox]'; ?>" <?php echo $checked ?>
                                        <?php echo $disabled; ?>
                                    />
                            <?php endif; ?>
                            <?php echo $componentName; ?>
                            <input type="button" value="<?php echo JText::_('PLG_JLNODUBLES_ADD_VAR'); ?>"
                                   onclick="shnodoubles.add_var(this, '<?php echo $component; ?>')"
                                   class="shnodoubles_add_var">
                        </div>
                        <?php
                        if (!isset($this->value[$component]) || !isset($this->value[$component]["var_name"])) {
                            $this->value[$component] = array(
                                'var_name' => array(''),
                                'var_value' => array('')
                            );
                        }

                        for ($i = 0; $i < sizeof($this->value[$component]["var_name"]); $i++) { ?>
                            <div class="sh_component_value">
                                <input name="<?php echo $this->name . '[' . $component . '][var_name][]'; ?>'"
                                       placeholder="<?php echo JText::_('PLG_JLNODUBLES_VAR'); ?>"
                                       value="<?php echo $this->value[$component]["var_name"][$i]; ?>">
                                <input name="<?php echo $this->name . '[' . $component . '][var_value][]'; ?>"
                                       placeholder="<?php echo JText::_('PLG_JLNODUBLES_COMPONENT_VAR_VALUE'); ?>"
                                       class="sh_component_var_value"
                                       value="<?php echo $this->value[$component]["var_value"][$i]; ?>">
                                <input type="button" value="<?php echo JText::_('PLG_JLNODUBLES_DEL'); ?>"
                                       onclick="shnodoubles.remove_var(this)" class="shnodoubles_remove_var">
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
        $script = '
            var jlnodoubles = {
                "name": "' . $this->name . '",
                "lang": {
                    "VAR": "' . JText::_('PLG_JLNODUBLES_VAR') . '",
                    "VALUE": "' . JText::_('PLG_JLNODUBLES_COMPONENT_VAR_VALUE') . '",
                    "DEL": "' . JText::_('PLG_JLNODUBLES_DEL') . '"
                }
            };
        ';

        $doc->addScriptDeclaration($script);
        $doc->addScript(JUri::root() . 'plugins/system/jlnodoubles/assets/jlnodoubles.min.js');
        $doc->addStyleSheet(JUri::root() . 'plugins/system/jlnodoubles/assets/jlnodoubles.min.css');
    }
}