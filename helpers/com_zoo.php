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

// load config
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

class JLNodoubles_com_zoo_helper extends JLNodoublesHelper
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
        $zoo = App::getInstance('zoo');
        $application = $zoo->zoo->getApplication();

        $item_id = $app->input->getInt('item_id', 0);
        $category_id = $app->input->getInt('category_id', 0);
        $page = $app->input->getInt('page', 0);
        $page = $page > 0 ? '&page='.$page : '';

        if(($allGet['view'] == 'item' && $allGet['layout'] == 'item') || $allGet['task'] == 'item')
        {
            if($item_id == 0){
                $menus = $app->getMenu();
                $menu = $menus->getActive();
                $item_id = $menu->params->get('item_id');
            }
            if($item_id == 0)
            {
                return false;
            }

            $item = $zoo->table->item->get($item_id);
            $original_link = JRoute::_($zoo->route->item($item, false), false);
        }
        else if(($allGet['view'] == 'category' && $allGet['layout'] == 'category') || $allGet['task'] == 'category')
        {
            if($category_id == 0){
                $menus = $app->getMenu();
                $menu = $menus->getActive();
                $category_id = $menu->params->get('category');
            }

            if($category_id == 0)
            {
                return false;
            }

            $categories = $application->getCategoryTree(true, $zoo->user->get(), true);
            $category   = $categories[$category_id];
            $link = $zoo->route->category($category, false).$page;
            $original_link = JRoute::_($link, false);
        }
        else if(($allGet['view'] == 'frontpage' && $allGet['layout'] == 'frontpage') || $allGet['task'] == 'frontpage')
        {
            $menus = $app->getMenu();
            $menu = $menus->getActive();
            $application_id = $menu->params->get('application');

            if($application_id == 0)
            {
                return false;
            }

            $link = $zoo->route->frontpage($application_id).$page;
            $original_link = JRoute::_($link, false);
        }
        else
        {
            return true;
        }

        if ($original_link && ($original_link != urldecode($currentLink)))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }
}
