<?php
/**
 * @package mod_jlnodubles
 * @author Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 1.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.error.error');

class plgSystemJlnodoubles extends JPlugin
{
    public static $noRedirect = null;
    public static $isPro = null;

    function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        if(is_null(self::$isPro))
        {
            self::$isPro = ($this->allow($this->params->get('key'))) ? true : false;
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

        $option =   $app->input->get('option', '', 'get');
        $Itemid =   $app->input->get('Itemid', 0, 'get', 'int');
        $view =     $app->input->get('view', 0, 'get');
        $id =       $app->input->get('id', 0, 'get', 'int');

        $allGet = array(
            'Itemid' => $Itemid,
            'option' => $option,
            'view' => $view,
            'id' => $id
        );

        $u = JUri::getInstance();
        $currentLink = $u->toString(array('path'));
        $uir = $_SERVER['REQUEST_URI'];


        $defValue = array(
            array(
                'var_name' => array(
                    'task',
                    'format',
                    'no_html',
                    'tmpl',
                ),
                'var_value' => array(
                    'save, edit, add, delete, apply',
                    'nohtml',
                    '1',
                    'component'
                )
            ),
            'com_content' => array(
                'checkbox' => 'on',
                'var_name' => array(''),
                'var_value' => array('')
            )
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

        if ($option && ($option != "com_content")) {
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
                $this->shRedirect($redirectLink);
            }

        }
        else if ($option == "com_content")
        {
            $original_link = '';
            $homealias = $this->params->get('homealias', 'home');
            $currentLink = $u->toString(array('path', 'query'));
            include_once(JPATH_SITE . '/components/com_content/helpers/route.php');

            switch ($view)
            {
                case 'article':
                    $original_link = '';
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
                    $original_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));

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
                    if ($app->input->getInt('showall') > 0) $original_link .= $symb . "showall=" . $app->input->getInt('showall');
                    break;

                case 'frontpage':
                    $original_link = JURI::base(true) . '/';
                    if ($app->input->getInt('start') > 0) $original_link .= "index.php?start=" . $app->input->getInt('start');
                    break;

                case 'category':
                    $original_link = JRoute::_(ContentHelperRoute::getCategoryRoute($allGet['id']));

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
            }

            if (($original_link != $currentLink) && $original_link)
            {
                $this->shRedirect($original_link);
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

//    public function onContentPrepare($context, &$article, &$params, $page = 0)
//    {
//        if (self::$noRedirect) return;
//
//
//    }

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

    private function shRedirect($link)
    {
        if ($this->params->get('301redirect', 1)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link);
        } else {
            JError::raiseError(404, JText::_('PLG_JLNODUBLES_NOPAGE'));
            return false;
        }
    }

    private function allow($key)
    {
        $allowedHost = (empty($key)) ? 'localhost' : $key;
        $allowedHost = explode('::', $allowedHost);
        $allow = false;
        foreach($allowedHost as $allowed) {
            $allowed = $this->dsCrypt($allowed, true);
            if(!empty($allowed)){
                $allowed = explode('|', $allowed);
                $site = (!empty($allowed[0])) ? $allowed[0] : 'localhost';
                $extension = (!empty($allowed[1])) ? $allowed[1] : '';
                $expireDate = (!empty($allowed[2])) ? $allowed[2] : '';
                if(strpos($_SERVER['HTTP_HOST'], $site) !== false && $extension == 'plg_system_jlnodoubles'){
                    $allow = true;
                    break;
                }
            }
        }
        return $allow;
    }

    private function dsCrypt($input,$decrypt=false)
    {
        $o = $s1 = $s2 = array();
        $basea = array('?','(','@',';','$','#',"]","&",'*');
        $basea = array_merge($basea, range('a','z'), range('A','Z'), range(0,9) );
        $basea = array_merge($basea, array('!',')','_','+','|','%','/','[','.',' ') );
        $dimension=9;
        for($i=0;$i<$dimension;$i++) {
            for($j=0;$j<$dimension;$j++) {
                $s1[$i][$j] = $basea[$i*$dimension+$j];
                $s2[$i][$j] = str_rot13($basea[($dimension*$dimension-1) - ($i*$dimension+$j)]);
            }
        }
        unset($basea);
        $m = floor(strlen($input)/2)*2;
        $symbl = $m==strlen($input) ? '':$input[strlen($input)-1];
        $al = array();
        for ($ii=0; $ii<$m; $ii+=2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii+1]);
            $a1 = $a2 = array();
            for($i=0;$i<$dimension;$i++) {
                for($j=0;$j<$dimension;$j++) {
                    if ($decrypt) {
                        if ($symb1===strval($s2[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s1[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s2[$i][$j])) $al=array($i,$j);
                    }
                    else {
                        if ($symb1===strval($s1[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s2[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s1[$i][$j])) $al=array($i,$j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
                $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1.$symbn2;
        }
        if (!empty($symbl) && sizeof($al))
            $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
        return implode('',$o);
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
            $uri = JUri::current();
            foreach($stopWords as $stopWord){
                if(strpos($uri, trim($stopWord)) !== false){
                   return true;
                }
            }
        }
        return false;
    }
}
