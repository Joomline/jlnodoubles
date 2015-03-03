<?php
/**
 * Created by PhpStorm.
 * User: Аркадий
 * Date: 03.03.2015
 * Time: 0:42
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
        $currentLink = $uri->toString(array('scheme', 'host','path', 'query'));

        $app = JFactory::getApplication();
        $zoo = App::getInstance('zoo');
        $application = $zoo->zoo->getApplication();

        $page = $app->input->getInt('page', 0);
        $item_id = $app->input->getInt('item_id', 0);
        $category_id = $app->input->getInt('category_id', 0);

        if(($allGet['view'] == 'item' && $allGet['layout'] == 'item') || $allGet['task'] == 'item')
        {
            if($item_id == 0){
                $menus = $app->getMenu();
                $menu = $menus->getActive();
                $item_id = $menu->params->get('item_id');
                $application_id = $menu->params->get('application');
            }
            if($item_id == 0)
            {
                return false;
            }

            $item = $zoo->table->item->get($item_id);
            $original_link = JRoute::_($zoo->route->item($item, false), false, -1);
        }
        else if(($allGet['view'] == 'category' && $allGet['layout'] == 'category') || $allGet['task'] == 'category')
        {
            if($category_id == 0){
                $menus = $app->getMenu();
                $menu = $menus->getActive();
                $category_id = $menu->params->get('category');
                $application_id = $menu->params->get('application');
            }
            if($category_id == 0)
            {
                return false;
            }

            $limitstring = '';
            if ($page > 0)
            {
                $limitstring .= "&page=" . $page;
            }

            $categories = $application->getCategoryTree(true, $zoo->user->get(), true);
            $category   = $categories[$category_id];
            $original_link = JRoute::_($zoo->route->category($category, false).$limitstring, false, -1);
        }
        else
        {
            return false;
        }

        if ($original_link && ($original_link != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }
}