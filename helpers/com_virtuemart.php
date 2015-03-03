<?php
/**
 * Created by PhpStorm.
 * User: Аркадий
 * Date: 03.03.2015
 * Time: 0:42
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

        switch ($allGet['view'])
        {
            case 'category':
                $limitString = '';
                if($limitstart > 0 || $limit > 0)
                {
                    $limitString .= '&limitstart='.$limitstart;
                }
                if($limit > 0)
                {
                    $limitString .= '&limit='.$limit;
                }
                if($category_id > 0)
                {
                    $original_link = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category_id.$limitString);
                }
                else if($manufacturer_id > 0)
                {
                    $original_link = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_manufacturer_id='.$manufacturer_id.$limitString);
                }
                break;
            case 'productdetails':
                $Itemid = $app->input->getInt('Itemid','');
                if(!empty($Itemid))
                {
                    $Itemid = '&Itemid='.$Itemid;
                }
                $original_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id='
                    . $category_id . '&virtuemart_product_id='.$product_id.$Itemid);
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