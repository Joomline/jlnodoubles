/**
 * @package plg_jlnodubles
 * @author Arkadiy (a.sedelnikov@gmail.com), Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 2.5.1
 * @copyright (C) 2014-2019 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */

var shnodoubles = {
    remove_var: function(self)
    {
        self.parentNode.parentNode.removeChild(self.parentNode);
    },

    add_var: function(self, component_id)
    {
        var new_var_wrapper = document.createElement('div');
        new_var_wrapper.className='sh_component_value';
        new_var_wrapper.innerHTML= '<input name="'+jlnodoubles.name+'['+component_id+'][var_name][]" placeholder="'+jlnodoubles.lang.VAR+'" />' +
        '<input name="'+jlnodoubles.name+'['+component_id+'][var_value][]" placeholder="'+jlnodoubles.lang.VALUE+'" class="sh_component_var_value">' +
        '<input type="button" value="'+jlnodoubles.lang.DEL+'" onclick="shnodoubles.remove_var(this)" class="shnodoubles_remove_var">';
        document.getElementById('sh_component_'+component_id).appendChild(new_var_wrapper);
    },

    com_checkbox: function(self, component_id)
    {
        var el=document.getElementById('sh_component_'+component_id);
        el.className=(self.checked)? 'sh_component_inner': 'sh_component_inner unchecked';
    }
};