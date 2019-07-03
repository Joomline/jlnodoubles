<?php
/**
 * @package plg_jlnodubles
 * @author Arkadiy (a.sedelnikov@gmail.com), Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 2.5.1
 * @copyright (C) 2014-2019 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
require_once JPATH_ROOT.'/administrator/components/com_hikashop/helpers/helper.php';
require_once JPATH_ROOT.'/components/com_hikashop/helpers/route.php';
class JLNodoubles_com_hikashop_helper extends JLNodoublesHelper
{

    function __construct($params)
    {
        parent::__construct($params);
    }

    public function go($allGet){
        $original_link = '';
        $app = JFactory::getApplication();
        $uri = JUri::getInstance();
        $currentLink = $uri->toString(array('path', 'query'));

	    $cid = $app->input->getInt('cid', 0);
	    $ctrl = $app->input->getCmd('ctrl', '');
	    $name = $app->input->getString('name', '');
		$page = '';

		if($allGet["view"] == 'category' && $allGet["layout"] == 'listing'){
			$page = 'categories';
		}
		else if($ctrl == 'category' && $allGet["task"] == 'listing'){
			$page = 'category';
		}
		else if($allGet["view"] == 'product' && $allGet["layout"] == 'listing'){
			$page = 'product_listing';
		}
		else if($ctrl == 'product' && $allGet["task"] == 'show'){
			$page = 'product';
		}

        switch ($page)
        {
            case 'categories':
	            $original_link = JRoute::_('index.php?option=com_hikashop&view=category&layout=listing');
	            break;
            case 'category':
	            $original_link = $this->getCategoryLink($cid, $name, $allGet["Itemid"]);
	            break;
            case 'product_listing':
	            $original_link = JRoute::_('index.php?option=com_hikashop&view=product&layout=listing');
	            break;
            case 'product':
	            if(empty($name)){
		            $name = $this->getProductName($cid);
	            }
	            $original_link = hikashopTagRouteHelper::getProductRoute($cid.':'.$name, 0, $allGet["lang"]);
	            break;
            case '':
	            return true;
                break;
        }

        if ($original_link && ($this->urlEncode($original_link) != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }

    private function getProductName($cid){
    	if(!$cid){
    		return '';
	    }
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('product_alias')->from('#__hikashop_product')->where('product_id = '.(int)$cid);
    	$result = $db->setQuery($query,0,1)->loadResult();
    	return (string)$result;
    }

	private function getCategoryLink($cid, $alias='', $Itemid=0) {
		if(!is_object($cid)){
			$obj = new stdClass();
			$obj->category_id = $cid;
			$obj->alias = $alias;
			$cid = $obj;
		}

		if(!empty($cid->override_url))
			return $cid->override_url;

		if(!empty($cid->link))
			return $cid->link;


		$config =& hikashop_config();
		if(!empty($Itemid) && $config->get('forward_to_submenus',1)){
			$app = JFactory::getApplication();
			$menus	= $app->getMenu();
			if(!HIKASHOP_J16){
				$query = 'SELECT a.id as itemid FROM `#__menu` as a WHERE a.access = 0 AND a.parent='.(int)$Itemid;
			}else{
				$query = 'SELECT a.id as itemid FROM `#__menu` as a WHERE a.client_id=0 AND a.parent_id='.(int)$Itemid;
			}
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$submenus = $db->loadObjectList();
			foreach($submenus as $submenu){
				$menu = $menus->getItem($submenu->itemid);
				if(!empty($menu) && !empty($menu->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false || strpos($menu->link,'view=product')!==false)){
					$parent = 0;
					if(HIKASHOP_J30){
						$params = $menu->params->get('hk_category',false);
						if($params && isset($params->category))
							$parent = $params->category;
					}
					if(!$parent){
						$params = $config->get( 'menu_'.$submenu->itemid );
						if(isset($params['selectparentlisting']))
							$parent = $params['selectparentlisting'];
					}

					if(!empty($params) && $parent == $cid->category_id){
						return JRoute::_('index.php?option=com_hikashop&Itemid='.$submenu->itemid);
					}
				}
			}
		}

		$type = 'category';
		if(!empty($this->menu_id)){
			$parts = explode('=',$this->menu_id);
			$app = JFactory::getApplication();
			$menus	= $app->getMenu();
			$menu = $menus->getItem($parts[1]);
			if(!empty($menu) && !empty($menu->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=')===false || strpos($menu->link,'view=product')!==false)){
				$type = 'product';
			}
		}
		return hikashop_contentLink($type.'&task=listing&cid='.$cid->category_id.'&name='.$cid->alias.$this->menu_id,$cid);
	}
}
