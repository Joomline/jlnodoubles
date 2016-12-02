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

if(is_file(JPATH_ROOT.'/components/com_jshopping/lib/factory.php'))
    require_once JPATH_ROOT.'/components/com_jshopping/lib/factory.php';

class JLNodoubles_com_jshopping_helper extends JLNodoublesHelper
{

    function __construct($params)
    {
        parent::__construct($params);
    }

    public function go($allGet)
    {
        $original_link = '';
        $app = JFactory::getApplication();
        $uri = JUri::getInstance();
        $currentLink = $uri->toString(array('path', 'query'));

        $product_id = $app->input->getInt('product_id', 0);
        $category_id = $app->input->getInt('category_id', 0);
        $manufacturer_id = $app->input->getInt('manufacturer_id', 0);
        $start = $app->input->getInt('start', 0);
        $task = $app->input->getCmd('task', '');
	$view = $app->input->getCmd('view', '');
	$controller = $app->input->getCmd('controller', '');
	$controller = empty($controller) ? $view : '';

        if(!in_array($task, array('view', 'category')))
        {
            return true;
        }

        $baseLink = 'index.php?option=com_jshopping&controller='.$controller.'&task=view';

        switch ($controller)
        {
            case 'category':
                $context = 'jshoping.list.front.product';
                $start = $this->getStart($start, $context);
                $original_link = SEFLink($baseLink . '&category_id='.$category_id.$start);
                break;

            case 'product':
                $original_link = SEFLink($baseLink . '&category_id='.$category_id.'&product_id='.$product_id);
                break;

            case 'manufacturer':
                $context = 'jshoping.manufacturlist.front.product';
                $start = $this->getStart($start, $context);
                $original_link = SEFLink($baseLink . '&manufacturer_id='.$manufacturer_id.$start, 2);
                break;

            default:
                return true;
                break;
        }

        if ($original_link && ($original_link != urldecode($currentLink)))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }

    private function getStart($start, $context)
    {
        if($start == 0)
        {
            return '';
        }

        $app = JFactory::getApplication();
        $limit = $app->getUserStateFromRequest($context.'limit', 'limit', JSFactory::getConfig()->count_products_to_page, 'int');

        if($start%$limit != 0)
        {
            $start = ((int)($start/$limit))*$limit;
        }

        $start = $start > 0 ? '&start='.$start : '';
        return $start;
    }
}
