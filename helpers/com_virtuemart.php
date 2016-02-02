<?php
/**
 * @package plg_jlnodubles
 * @author Arkadiy (a.sedelnikov@gmail.com), Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 1.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die;



class JLNodoubles_com_virtuemart_helper extends JLNodoublesHelper
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
        $product_id = $app->input->getInt('virtuemart_product_id', 0);
        $category_id = $app->input->getInt('virtuemart_category_id', 0);
        $manufacturer_id = $app->input->getInt('virtuemart_manufacturer_id', 0);
        $limitstart = $app->input->getInt('limitstart', 0);
        $limit = $app->input->getInt('limit', 0);
        $orderby = $app->input->getString('orderby', '');
		$keyword = $app->input->getString('keyword', '');
		$view = $allGet['view'];

        $menus = $app->getMenu();
        $menu = $menus->getActive();

        switch ($view)
        {
            case 'category':
			case 'manufacturer':
			
				if($app->input->getString('search','') == 'true')
				{
					return true;
				}
				
				$Itemid = $app->input->getInt('Itemid',0);
                $Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

                $limitString = '';

                if($limit > 0)
                {
                    $limitString .= '&limit='.$limit;
                }

                if($limitstart > 0 || $limit > 0)
                {
                    $limitString .= '&limitstart='.$limitstart;
                }

                $orderbyString = !empty($orderby) ? '&orderby='.$orderby : '';

                if($menu->query['option'] == 'com_virtuemart'
                    && $menu->query['view'] == $view
                    && $menu->query['virtuemart_category_id'] == $category_id
                    && $menu->query['virtuemart_manufacturer_id'] == $manufacturer_id)
                {
                    $original_link = '/'.$menu->route;
                }
				else if($category_id > 0 && $manufacturer_id > 0)
				{
                    $original_link = JRoute::_ ( 'index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id=' . $category_id.'&virtuemart_manufacturer_id='.$manufacturer_id.$orderbyString.$limitString.$Itemid);
				}
                else if($category_id > 0)
                {
                    $original_link = JRoute::_ ( 'index.php?option=com_virtuemart&view='.$view.'&virtuemart_category_id=' . $category_id.$orderbyString.$limitString.$Itemid);
                }
                else if($manufacturer_id > 0)
                {
                    $original_link = JRoute::_('index.php?option=com_virtuemart&view=' . $view . '&virtuemart_manufacturer_id=' . $manufacturer_id . $orderbyString . $limitString . $Itemid, false);
                }
				else if(!empty($keyword))
				{
					return true;
				}
                else if($menu->query['option'] == 'com_virtuemart' && $menu->query['view'] == $view)
                {
                    $original_link = JRoute::_('index.php?option=com_virtuemart&view='.$view.$orderbyString.$limitString.'&Itemid='.$menu->id, false);
                }
                else{
                    return true;
                }

                break;

            case 'productdetails':
			
				if($allGet['task'] == 'askquestion')
				{
					return true;
				}
				
                $Itemid = $app->input->getInt('Itemid','');

                $ItemidString = '';
                if(!empty($Itemid))
                {
                    $ItemidString = '&Itemid='.$Itemid;
                }

                $categoryString = '';
                if($category_id)
                {
                    $categoryString = '&virtuemart_category_id=' . $category_id;
                }

                $link = 'index.php?option=com_virtuemart&view=productdetails'
                    . $categoryString . '&virtuemart_product_id='.$product_id.$ItemidString;

                $original_link = JRoute::_($link, false);
                break;

            case 'categories':
                //$link = 'index.php?option=com_virtuemart&view=categories&virtuemart_category_id=' . $category_id;
                //$original_link = JRoute::_($link, false);
                return true;
                break;

            default:
                return true;
                break;
        }

        if ($original_link && ($original_link != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }
}
