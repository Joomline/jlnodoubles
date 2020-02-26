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
require_once JPATH_ROOT.'/administrator/components/com_phocacart/libraries/phocacart/route/route.php';

class JLNodoubles_com_phocacart_helper extends JLNodoublesHelper
{

    function __construct($params)
    {
        parent::__construct($params);
    }

    public function go($allGet)
    {
        $app = JFactory::getApplication();
        $uri = JUri::getInstance();
        $currentLink = $uri->toString(array('path', 'query'));

        $id = (int)$app->input->getInt('id', 0);
	    $catid = (int)$app->input->getInt('catid', 0);

        switch ($allGet["view"])
        {
            case 'categories':
                $original_link = JRoute::_(PhocacartRoute::getCategoriesRoute());
	            break;
            case 'category':
                $routeInfo = PhocacartRoute::getIdForItemsRoute();
	            $original_link = JRoute::_(PhocacartRoute::getCategoryRoute($routeInfo['id'], $routeInfo['alias']));
	            break;
            case 'items':
                $routeInfo = PhocacartRoute::getIdForItemsRoute();
	            $original_link = JRoute::_(PhocacartRoute::getItemsRoute($routeInfo['id'], $routeInfo['alias']));
	            break;
            case 'item':
                $product = PhocacartProduct::getProduct($id);
                $alias = !empty($product->alias) ? $product->alias : '';
                $catAlias = !empty($product->catalias) ? $product->catalias : '';
	            $original_link = JRoute::_(PhocacartRoute::getItemRoute($id, $catid, $alias, $catAlias));
	            break;
            default:
	            return true;
                break;
        }

        if ($original_link && ($this->urlEncode($original_link) != $currentLink))
        {
            $this->shRedirect($original_link);
        }
        return true;
    }


}
