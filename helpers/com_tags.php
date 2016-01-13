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

require_once JPATH_ROOT . '/components/com_tags/helpers/route.php';

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

        $tagId = $app->input->getString('id', '');

        switch ($allGet['view'])
        {
            case 'tags':
                $original_link = JRoute::_('index.php?option=com_tags&view=tags', false);
                break;
            case 'tag':
                $original_link = JRoute::_(TagsHelperRoute::getTagRoute($tagId));
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
