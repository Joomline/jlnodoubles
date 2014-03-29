<?php
 /**
 * @package mod_jlnodubles
 * @author Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 1.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
*/
defined('_JEXEC') or die;


jimport('joomla.plugin.plugin');
jimport( 'joomla.error.error' );

class plgSystemJlnodoubles extends JPlugin
{

	public static $noRedirect = null;

	function onAfterRoute(){
		$app = JFactory::getApplication();
		if($app->getName() != 'site') {
			return true;
		}

		$option=JFactory::getApplication()->input->get('option', '', 'get');
		$Itemid=JFactory::getApplication()->input->get('Itemid', 0, 'get', 'int');
		$view=JFactory::getApplication()->input->get('view', 0, 'get');
		$id=JFactory::getApplication()->input->get('id', 0, 'get','int');

		//$allGet=JFactory::getApplication()->input->get('get');
		$allGet = array(
			'Itemid'       => $Itemid,		
			'option'       => $option,		
			'view'       => $view,		
			'id'       => $id	
		);
		//echo '<pre>allGet1::'.print_r($allGet,true).'</pre>';
		//$allGet=JRequest::get('get');
		
		$u = JURI::getInstance();
		$currentLink=$u->toString( array(  'path' ) );	
		$uir=$_SERVER['REQUEST_URI'];
	


		$defvalue=array();
		$defvalue[0]=array();
		$defvalue['com_content']=array();
		$defvalue[0]["var_name"][0]='task';
		$defvalue[0]["var_value"][0]='save, edit, add, delete, apply';

		$defvalue[0]["var_name"][1]='format';
		$defvalue[0]["var_value"][1]='nohtml';

		$defvalue[0]["var_name"][2]='no_html';
		$defvalue[0]["var_value"][2]='1';

		$defvalue[0]["var_name"][3]='tmpl';
		$defvalue[0]["var_value"][3]='component';

		$defvalue['com_content']["checkbox"]='on';
		$defvalue['com_content']["var_name"][0]='';
		$defvalue['com_content']["var_value"][0]='';

		$componentsvars = $this->params->get('componentsvars', $defvalue);

		if (is_object($componentsvars)) {
			$componentsvars_array=array();
			foreach($componentsvars as $k=>$val){

				if(is_object($val)){
					$val_arr=array();
					foreach($val as $kk=>$vv){
						$val_arr[$kk]=$vv;
					}
					$val=$val_arr;
				}
				$componentsvars_array[$k]=$val;
			}
			$componentsvars=$componentsvars_array;
		}


		if(!isset($componentsvars[$option]["checkbox"])){
			self::$noRedirect=true;
			return;
		}

		$cur_components=array(0, $option);
		
		foreach($cur_components as $comp){
			if(isset($componentsvars[$comp])){
				for($i=0; $i< sizeof($componentsvars[$comp]["var_name"]); $i++){
					$var_name=$componentsvars[$comp]["var_name"][$i];
					
					if(trim($componentsvars[$comp]["var_value"][$i])){

						$var_values= array_map('trim', explode(',', $componentsvars[$comp]["var_value"][$i]));
						
						foreach($var_values as $vvalue){
							if(isset($allGet[trim($var_name)]) 
							&& $allGet[trim($var_name)]==trim($vvalue)){ 
							self::$noRedirect=true;
							return;
							}
						}
					}else if(isset($allGet[$var_name])){
						self::$noRedirect=true;
						return;
					}
				}
			}
		}

		if($option && ($option!="com_content")){

			$allGetArr=array();
			foreach($allGet as $ag_name=>$ag_value){
			  if($ag_name && $ag_value)$allGetArr[]=$ag_name.'='.$ag_value;
			}

			if(!$Itemid){
				$app		= JFactory::getApplication();
				$menus = $app->getMenu();
			    //$menus = &JApplication::getMenu('site', array());
			    $component_menu=$menus->getItems('component',$option);
			    $Itemid=$component_menu[0]->id;
			    if($Itemid) $allGetArr[]='Itemid='.$Itemid;
			}

			/*if (JRequest::getInt('start') > 0){
				 $currentLink .= "?start=" . JRequest::getVar('start');
			}		
			if (JRequest::getInt('limitstart') > 0){
				 $currentLink .= "?limitstart=" . JRequest::getVar('limitstart');
			}*/	

			$redirectLink=JRoute::_('index.php?'.implode('&', $allGetArr));
			if($redirectLink != $currentLink){
				$this->shRedirect($redirectLink);
			}

		}else if($option=="com_content"){
				//$view=JRequest::getVar('view', '', 'get');				
				if($view=='category'){
					include_once(JPATH_SITE.'/components/com_content/helpers/route.php');
					
					$catlink=JRoute::_(ContentHelperRoute::getCategoryRoute($allGet['id']));
					//echo '<pre>allGet1::'.print_r($catlink,true).'</pre>';
				
					if (JRequest::getInt('start') > 0){
						 $catlink .= "?start=" . JRequest::getVar('start');
						 $currentLink .= "?start=" . JRequest::getVar('start');
					}
					if($catlink != $currentLink){					
						$this->shRedirect($catlink);
					}
				}
		}

		return true;
	}


	function onAfterRender()
	{

		$app =JFactory::getApplication();
		$homealias=$this->params->get('homealias', 'home');

		if($app->getName() != 'site') {
			return true;
		}
		$buffer = JResponse::getBody();
		$regex  = '#component/content/article/#m';
		$buffer=preg_replace($regex,$homealias.'/',$buffer);
		JResponse::setBody($buffer);
		return true;
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{

		$real_link = JRequest::getURI();		
		$original_link='';
		$option=JFactory::getApplication()->input->get('option', '', 'get');
		$view=JFactory::getApplication()->input->get('view', 0, 'get');
		$layout=JFactory::getApplication()->input->get('layout', 0, 'get');
		$homealias=$this->params->get('homealias', 'home');
		if(self::$noRedirect) return;
		if($option=='com_content'){
		    switch($view){
		      case 'article':			  
			$original_link = (isset($article->readmore_link)) ? $article->readmore_link : '';
			if(!$original_link) return;
			if(strpos($original_link, 'component/content/article')!== false) $original_link= str_replace('component/content/article', $homealias, $original_link);
			  $symb="?";
			  
			  if (JRequest::getInt('start') > 0){ 
				$original_link .= $symb."start=".JRequest::getVar('start');	
				$symb="&";
			  }
			  if (JRequest::getInt('showall') > 0) $original_link .= $symb."showall=".JRequest::getVar('showall');
		      break;
		      case 'frontpage':
			  $original_link = JURI::base(true).'/';
			  if (JRequest::getInt('start') > 0) $original_link .= "index.php?start=".JRequest::getVar('start');
		      break;
		    }

		    if (($original_link != $real_link) && $original_link){
			$this->shRedirect($original_link);
		    } 
		}
	}

	public function shRedirect($link){
	      if($this->params->get('301redirect', 1)){
        	   header( 'HTTP/1.1 301 Moved Permanently' );
        	   header( 'Location: ' . $link );
	      }else {
			JError::raiseError( 404, JText::_( 'PLG_JLNODUBLES_NOPAGE' ) );
			return false;
			}
	}


	function onAfterInitialise()
	{

		$application = JFactory::getApplication();
		$router = $application->getRouter();
		if($router->getMode() == JROUTER_MODE_SEF) {
			$router->attachBuildRule(array(&$this, 'build'));
			//$router->attachParseRule(array(&$this, 'parse'));
		}
	}


	function build(&$router, &$uri)
	{
				// Get the route
		$route = $uri->getPath();

		// Get the query data
		$query = $uri->getQuery(true);

		if($query['option']=='com_content'){
			$toUnset=array('showall', 'start', 'limitstart');
			foreach($toUnset as $tu){
				if(isset($query[$tu]) && !$query[$tu]) unset($query[$tu]);
			}	
		}else return;


		//Set query again in the URI
		$uri->setQuery($query);
		$uri->setPath($route);
	}

}
