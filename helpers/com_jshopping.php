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
        $controller = $app->input->getCmd('controller', '');
        $task = $app->input->getCmd('task', '');

        if($task != 'view')
        {
            return true;
        }

        $baseLink = 'index.php?option=com_jshopping&controller='.$controller.'&task=view';

        switch ($controller)
        {
            case 'category':
                $original_link = SEFLink($baseLink . '&category_id='.$category_id);
                break;

            case 'product':
                $original_link = SEFLink($baseLink . '&category_id='.$category_id.'&product_id='.$product_id);
                break;

            case 'manufacturer':
                $original_link = SEFLink($baseLink . '&manufacturer_id='.$manufacturer_id, 2);
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
