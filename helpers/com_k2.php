<?php
/**
 * Created by PhpStorm.
 * User: Аркадий
 * Date: 03.03.2015
 * Time: 0:42
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
        JTable::addIncludePath(JPATH_ROOT.'/administrator/components/com_k2/tables');

        if($allGet['view'] == 'item' && $allGet['task'] == '')
        {
            $item = JTable::getInstance('K2Item', 'Table');
            $item->load($allGet['id']);
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load($item->catid);
            $original_link = JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($category->alias)));
        }
        else if($allGet['view'] == 'itemlist' && $allGet['task'] == 'category')
        {
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load((int)$allGet['id']);
            $original_link = JRoute::_(K2HelperRoute::getCategoryRoute((int)$allGet['id'].':'.urlencode($category->alias)));
        }
        else if($allGet['view'] == 'itemlist' && $allGet['task'] == 'user')
        {
            $original_link = JRoute::_(K2HelperRoute::getUserRoute((int)$allGet['id']));
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