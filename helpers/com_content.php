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

require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

class JLNodoubles_com_content_helper extends JLNodoublesHelper
{

    function __construct($params)
    {
        parent::__construct($params);
    }

    public function go($allGet){
        $original_link = '';
        $app = JFactory::getApplication();
        $uri = JUri::getInstance();
        $homealias = $this->params->get('homealias', 'home');
        $currentLink = $uri->toString(array('path', 'query'));

        switch ($allGet['view'])
        {
            case 'article':
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('`id`, `alias`, `catid`, `language`')
                    ->from('#__content')
                    ->where('`id` = '.(int)$allGet['id']);
                $item = $db->setQuery($query,0,1)->loadObject();

                if(is_null($item))
                {
                    return true;
                }

                $item->slug	= $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                $original_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language), false);

                if (!$original_link)
                {
                    return true;
                }

                if (strpos($original_link, 'component/content/article') !== false && !empty($homealias))
                {
                    $original_link = str_replace('component/content/article', $homealias, $original_link);
                }

                $symb = "?";

                if ($app->input->getInt('start') > 0)
                {
                    $original_link .= $symb . "start=" . $app->input->getInt('start');
                    $symb = "&";
                }

                if ($app->input->getInt('showall') > 0)
                {
                    $original_link .= $symb . "showall=" . $app->input->getInt('showall');
                }
                break;

            case 'frontpage':
            case 'featured':
                $link = 'index.php?option=com_content&view=' . $allGet['view'];
                if ($app->input->getInt('start') > 0)
                {
                    $link .= '&start=' . $app->input->getInt('start');
                }
                $original_link = JRoute::_($link);
                break;

            case 'category':
                $original_link = JRoute::_(ContentHelperRoute::getCategoryRoute($allGet['id']), false);

                $start = $app->input->getInt('start', 0);
                if ($start > 0)
                {
                    $limits = $this->params->get('limits',5);
                    if($start % $limits != 0)
                    {
                        $start = intval($start / $limits) * $limits;
                    }
                    $original_link .= "?start=" . $start;
                }
                break;
            case 'form':
                return true;
                break;
            default:
                return false;
                break;
        }

        if ($original_link && ($original_link != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }
}