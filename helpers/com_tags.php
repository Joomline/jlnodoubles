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

require_once JPATH_ROOT . '/components/com_tags/helpers/route.php';
use Joomla\Utilities\ArrayHelper;

class JLNodoubles_com_tags_helper extends JLNodoublesHelper
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



        $start = $app->input->getInt('start', 0);
        $start = ($start>0) ? '&start='.$start : '';

        switch ($allGet['view'])
        {
            case 'tags':
                $original_link = JRoute::_('index.php?option=com_tags&view=tags'.$start, false);
                break;
            case 'tag':
                $tagIds = $app->input->get('id', array(), 'array');

                if(count($tagIds) == 1){
                    $tagId = array_shift($tagIds);
                    $parts = explode(':', $tagId);
                    if(empty($parts[1])){
	                    $tagId = (int)$tagId.':'.$this->getAlas($tagId);
                    }
                    $original_link = JRoute::_(TagsHelperRoute::getTagRoute($tagId).$start, false);
                }
                else if(count($tagIds) > 1)
                {
                    $tagString = 'index.php?option=com_tags&view=tag';
                    foreach ($tagIds as $key => $tagId)
                    {
	                    $parts = explode(':', $tagId);
	                    if(empty($parts[1])){
		                    $tagId = (int)$tagId.':'.$this->getAlas($tagId);
	                    }
	                    
                        $tagString .= '&id['.$key.']='.$tagId;
                    }

                    $original_link = JRoute::_($tagString.$start, false);
                }

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

    private function getAlas($tagId){
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('alias')->from('#__tags')->where('id = '.(int)$tagId);
    	return (string)$db->setQuery($query,0,1)->loadResult();
    }
}
