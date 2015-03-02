<?php
/**
 * Created by PhpStorm.
 * User: Аркадий
 * Date: 03.03.2015
 * Time: 1:11
 */
defined('_JEXEC') or die;

class JLNodoublesHelper{

    private static $instance;
    protected $params;

    function __construct($params)
    {
        $this->params = $params;
    }

    static public function getInstance($params)
    {
        if (!is_object(self::$instance))
        {
            self::$instance = new JLNodoublesHelper($params);
        }
        return self::$_instance;
    }

    protected function shRedirect($link)
    {
        if ($this->params->get('301redirect', 1)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link);
        } else {
            JError::raiseError(404, JText::_('PLG_JLNODUBLES_NOPAGE'));
            return false;
        }
    }
}