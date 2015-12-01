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

jimport('joomla.plugin.plugin');
jimport('joomla.error.error');
require_once JPATH_ROOT . '/plugins/system/jlnodoubles/helpers/helper.php';

class plgSystemJlnodoubles extends JPlugin
{
    public static $noRedirect = null;
    public static $isPro = null;

    function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        if(is_null(self::$isPro))
        {
            self::$isPro = (JLNodoublesHelper::allow($this->params->get('key'))) ? true : false;
        }

        self::$noRedirect = $this->stopWords();
    }

    public function onAfterRoute()
    {
        $app = JFactory::getApplication();
        if ($app->getName() != 'site' || self::$noRedirect)
        {
            return true;
        }

        $option =   $app->input->getCmd('option', '');
        $Itemid =   $app->input->getInt('Itemid', 0);
        $view =     $app->input->getCmd('view', '');
        $id =       $app->input->getString('id', 0);
        $task =     $app->input->getCmd('task', '');
        $layout =   $app->input->getCmd('layout', '');
        $lang =     $app->input->getCmd('lang', '');
        $tmpl =     $app->input->getCmd('tmpl', '');

        $allGet = array(
            'Itemid' => $Itemid,
            'option' => $option,
            'view' => $view,
            'id' => $id,
            'task' => $task,
            'layout' => $layout,
            'lang' => $lang,
            'tmpl' => $tmpl
        );

        $u = JUri::getInstance();
        $currentLink = $u->toString(array('path'));
        $uir = $_SERVER['REQUEST_URI'];


        $defValue = array(
            array(
                'var_name' => array( 'task', 'format', 'no_html', 'tmpl' ),
                'var_value' => array( 'save, edit, add, delete, apply', 'nohtml', '1', 'component' )
            ),
            'com_content' => array( 'checkbox' => 'on', 'var_name' => array(''), 'var_value' => array('') )
        );

        $componentsvars = $this->params->get('componentsvars', $defValue);

        if (is_object($componentsvars))
        {
            $tmp = array();
            foreach ($componentsvars as $k => $val)
            {
                $tmp[$k] = (array)$val;
            }
            $componentsvars = $tmp;
            unset($tmp);
        }

        if (!isset($componentsvars[$option]["checkbox"]))
        {
            self::$noRedirect = true;
            return true;
        }

        if (isset($componentsvars[$option]))
        {
            for ($i = 0; $i < sizeof($componentsvars[$option]["var_name"]); $i++)
            {
                $var_name = $componentsvars[$option]["var_name"][$i];

                if (trim($componentsvars[$option]["var_value"][$i]))
                {
                    $var_values = array_map('trim', explode(',', $componentsvars[$option]["var_value"][$i]));

                    foreach ($var_values as $vvalue)
                    {
                        if (isset($allGet[trim($var_name)])
                            && $allGet[trim($var_name)] == trim($vvalue)
                        ) {
                            self::$noRedirect = true;
                            return true;
                        }
                    }
                }
                else if (isset($allGet[$var_name]))
                {
                    self::$noRedirect = true;
                    return true;
                }
            }
        }

        $helperPath = JPATH_ROOT.'/plugins/system/jlnodoubles/helpers/'.$option.'.php';
        $return = true;

        if(is_file($helperPath) && ($option == 'com_content' || self::$isPro))
        {
            require_once $helperPath;
            $class = 'JLNodoubles_' . $option . '_helper';
            if(class_exists($class))
            {
                $this->params->set('isPro', self::$isPro);
                $helper = new $class($this->params);
                if(method_exists($helper, 'go'))
                {
                    $return = $helper->go($allGet);
                }
            }
        }

        if ($option && $return == false)
        {
            $allGetArr = array();
            foreach ($allGet as $ag_name => $ag_value) {
                if ($ag_name && $ag_value) $allGetArr[] = $ag_name . '=' . $ag_value;
            }

            if (!$Itemid)
            {
                $app = JFactory::getApplication();
                $menus = $app->getMenu();
                //$menus = &JApplication::getMenu('site', array());
                $component_menu = $menus->getItems('component', $option);
                $Itemid = $component_menu[0]->id;
                if ($Itemid) $allGetArr[] = 'Itemid=' . $Itemid;
            }

            $redirectLink = JRoute::_('index.php?' . implode('&', $allGetArr));
            if ($redirectLink != $currentLink) {
                JLNodoublesHelper::getInstance($this->params)->shRedirect($redirectLink);
            }
        }

        return true;
    }

    public function onAfterRender()
    {
        if (JFactory::getApplication()->getName() != 'site' || self::$noRedirect)
        {
            return true;
        }

        $homealias = $this->params->get('homealias', 'home');
        $buffer = JResponse::getBody();
        $regex = '#component/content/article/#m';
        $buffer = preg_replace($regex, $homealias . '/', $buffer);
        JResponse::setBody($buffer);
        return true;
    }

    public function onAfterInitialise()
    {
        $application = JFactory::getApplication();
        $router = $application->getRouter();
        if ($router->getMode() == JROUTER_MODE_SEF) {
            $router->attachBuildRule(array(&$this, 'build'));
            //$router->attachParseRule(array(&$this, 'parse'));
        }
    }

    public function build(&$router, &$uri)
    {
        // Get the route
        $route = $uri->getPath();

        // Get the query data
        $query = $uri->getQuery(true);

        if ($query['option'] == 'com_content')
        {
            $toUnset = array('showall', 'start', 'limitstart');
            foreach ($toUnset as $tu)
            {
                if (isset($query[$tu]) && !$query[$tu]) unset($query[$tu]);
            }
        } else return;

        //Set query again in the URI
        $uri->setQuery($query);
        $uri->setPath($route);
    }

    private function stopWords()
    {
        $stopWords = $this->params->get('stop_words','');
        if(empty($stopWords))
        {
            return false;
        }
        $stopWords = explode("\n", $stopWords);
        if(count($stopWords)){
            $u = JUri::getInstance();
            $uri = $u->toString(array('path', 'query'));
            foreach($stopWords as $stopWord)
            {
                $stopWord = trim($stopWord);
                if(empty($stopWord))
                {
                    continue;
                }
                if(strpos($uri, $stopWord) !== false){
                   return true;
                }
            }
        }
        return false;
    }
}
