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


class JFormFieldComponents extends JFormField {

    protected function getInput(){

	$db = & JFactory::getDBO();
	$doc = & JFactory::getDocument();
	$db->setQuery( 'SELECT element AS value, element AS text FROM #__extensions WHERE type="component" ORDER BY name' );
	$components = $db->loadObjectList();

	jimport( 'joomla.filesystem.folder' );
	$folders=JFolder::folders(JPATH_ROOT.'/components', 'com_(.*)');

	if(!$this->value){
		$this->value=array();
		$this->value[0]=array();
		$this->value['com_content']=array();
		$this->value[0]["var_name"][0]='task';
		$this->value[0]["var_value"][0]='save, edit, add, delete, apply';

		$this->value[0]["var_name"][1]='format';
		$this->value[0]["var_value"][1]='nohtml';

		$this->value[0]["var_name"][2]='no_html';
		$this->value[0]["var_value"][2]='1';

		$this->value[0]["var_name"][3]='tmpl';
		$this->value[0]["var_value"][3]='component';

		$this->value['com_content']["checkbox"]='on';
		$this->value['com_content']["var_name"][0]='';
		$this->value['com_content']["var_value"][0]='';
	}

	echo '<div id="sh_component_wrapper">';
	$all=new stdClass();
	$all->value=0; $all->text=JText::_('PLG_JLNODUBLES_ALL_COMPONENTS');
	array_unshift($components, $all);
	$odd_even=1;
	foreach ($components as $component){
		if(in_array($component->text, $folders) || !$component->value){
		$odd_even=1-$odd_even;

		$checked=(isset($this->value[$component->value]["checkbox"]))? ' checked="checked" ' : '';
		$unchecked_class=($checked || !$component->value)? '' : ' unchecked ';
		echo '<div class="sh_component_inner row'.$odd_even.' '.$unchecked_class.'" id="sh_component_'.$component->value.'" rel="'.$odd_even.'">';
			echo '<div class="sh_component_name">';
			if($component->value) echo '<input type="checkbox" onclick="shnodoubles_com_checkbox(this, \''.$component->value.'\')" class="shnodoubles_com_checkbox" name="'.$this->name.'['.$component->value.'][checkbox]" '.$checked.'>';
			echo $component->text;
			echo '<input type="button" value="'.JText::_('PLG_JLNODUBLES_ADD_VAR').'" onclick="shnodoubles_add_var(this, \''.$component->value.'\')" class="shnodoubles_add_var">';
			echo '</div>';

			if(!isset($this->value[$component->value])){
				$this->value[$component->value]=array();
			}
			if(!isset($this->value[$component->value]["var_name"])){
				$this->value[$component->value]["var_name"]=array();
				$this->value[$component->value]["var_value"]=array();
				$this->value[$component->value]["var_name"][0]='';
				$this->value[$component->value]["var_value"][0]='';
			}

			for($i=0; $i< sizeof($this->value[$component->value]["var_name"]); $i++){
				echo '<div class="sh_component_value">';
					echo '<input name="'.$this->name.'['.$component->value.'][var_name][]" placeholder="'.JText::_('PLG_JLNODUBLES_VAR').'" value="'.$this->value[$component->value]["var_name"][$i].'">';
					echo '<input name="'.$this->name.'['.$component->value.'][var_value][]" placeholder="'.JText::_('PLG_JLNODUBLES_COMPONENT_VAR_VALUE').'" class="sh_component_var_value"  value="'.$this->value[$component->value]["var_value"][$i].'">';
					echo '<input type="button" value="'.JText::_('PLG_JLNODUBLES_DEL').'" onclick="shnodoubles_remove_var(this)" class="shnodoubles_remove_var">';
				echo '</div>';
			}
			
		echo '</div>';
		}
	}
	echo '</div>';



	$script="	
		function shnodoubles_remove_var(self){
			self.parentNode.parentNode.removeChild(self.parentNode);
		}
		function shnodoubles_add_var(self, component_id){
			var new_var_wrapper = document.createElement('div');
			new_var_wrapper.className='sh_component_value';
			new_var_wrapper.innerHTML= '<input name=\"".$this->name."['+component_id+'][var_name][]\" placeholder=\"".JText::_('PLG_JLNODUBLES_VAR')."\" /><input name=\"".$this->name."['+component_id+'][var_value][]\" placeholder=\"".JText::_('PLG_JLNODUBLES_COMPONENT_VAR_VALUE')."\" class=\"sh_component_var_value\"><input type=\"button\" value=\"".JText::_('PLG_JLNODUBLES_DEL')."\" onclick=\"shnodoubles_remove_var(this)\" class=\"shnodoubles_remove_var\">';
			document.getElementById('sh_component_'+component_id).appendChild(new_var_wrapper);
		}
		function shnodoubles_com_checkbox(self, component_id){
			var el=document.getElementById('sh_component_'+component_id);
			var oddeven = el.getAttribute('rel');
			el.className=(self.checked)? 'sh_component_inner row'+oddeven : 'sh_component_inner unchecked row'+oddeven;
		}
		
	";

	$doc->addScriptDeclaration($script);

	$style="
		.shnodoubles_add_var{
			float:none !important;
			margin-left:10px;
		}
		.sh_component_inner, #sh_component_wrapper, .sh_component_value{
			clear:both;
		}
		.sh_component_var_value{
			width:300px;
		}
		.sh_component_name{
			color:purple;
			font-weight:bold;
		}
		.shnodoubles_add_var{
			color:green;
			font-weight:bold;
		}
		.shnodoubles_remove_var{
			color:#b00;
			font-weight:bold;
		}
		.sh_component_inner.unchecked .sh_component_value, .sh_component_inner.unchecked .shnodoubles_add_var{
			display:none;
		}
		.sh_component_inner.row1{
			background:#eee;
			border-top:1px solid #ccc;
			border-bottom:1px solid #ccc;
		}
		.sh_component_inner{
			padding:5px;
		}
		.sh_component_inner input{
			float:none;
		}
	";

	$doc->addStyleDeclaration($style);

    }

}



