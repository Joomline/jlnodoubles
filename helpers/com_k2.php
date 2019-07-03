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

require_once JPATH_ROOT . '/components/com_k2/helpers/route.php';

class JLNodoubles_com_k2_helper extends JLNodoublesHelper
{

    function __construct($params)
    {
        parent::__construct($params);
    }

    public function go($allGet)
    {
        $uri = JUri::getInstance();
        $currentLink = $uri->toString(array('path', 'query'));
	    $app = JFactory::getApplication();
        $start = $app->input->getInt('start', 0);
	    $layout = $app->input->getCmd('layout', '');
        JTable::addIncludePath(JPATH_ROOT.'/administrator/components/com_k2/tables');

        $limitstring = '';
        if ($start > 0)
        {
            $limits = $this->params->get('limits',5);
            if($start % $limits != 0)
            {
                $start = intval($start / $limits) * $limits;
            }
            $limitstring .= "?start=" . $start;
        }

        if($allGet['view'] == 'item' &&  ($allGet['task'] == '' || $allGet['task'] == $allGet['id']))
        {
            $item = JTable::getInstance('K2Item', 'Table');
            $item->load($allGet['id']);
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load($item->catid);
            $original_link = JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($category->alias)), false);
        }
        else if($allGet['view'] == 'itemlist' && $allGet['task'] == 'category')
        {
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load((int)$allGet['id']);
            $original_link = JRoute::_(K2HelperRoute::getCategoryRoute((int)$allGet['id'].':'.urlencode($category->alias)), false).$limitstring;
        }
        else if($allGet['view'] == 'itemlist' && $allGet['task'] == 'user')
        {
	        JLoader::register('K2HelperUtilities', JPATH_SITE.'/components/com_k2/helpers/utilities.php');
	        $original_link = JRoute::_(K2HelperRoute::getUserRoute((int)$allGet['id']), false).$limitstring;
        }
        else if($allGet['view'] == 'itemlist' && $allGet['task'] == 'tag')
        {
            $tag = JFactory::getApplication()->input->getString('tag');
            $original_link = JRoute::_(K2HelperRoute::getTagRoute($tag), false).$limitstring;
        }
        else if($allGet['view'] == 'itemlist' && $layout == 'category')
        {
	        $menus = $app->getMenu();
	        $menu = $menus->getActive();

	        if($menu->query['option'] == 'com_k2' && $menu->query['view'] == $allGet['view'] && $menu->query['layout'] == $layout)
	        {
		        $link = 'index.php?option=com_k2&Itemid='.$menu->id;
		        $original_link = JRoute::_($link, false);
	        }
	        else{
		        $Itemid = $app->input->getInt('Itemid', 0);
		        if($Itemid > 0){
			        $limitstring .= '&Itemid='.$Itemid;
		        }
		        $link = 'index.php?option=com_k2&view=itemlist&layout=category'.$limitstring;

		        $original_link = JRoute::_($link, false);
	        }

        }
        else
        {
            return true;
        }

	    $currentLink = urldecode($currentLink);
	    $original_link = urldecode($original_link);

        if ($original_link && ($original_link != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }
}
