<?php
/**
 * @package mod_jlnodubles
 * @author Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 2.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
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
        return self::$instance;
    }

    public function shRedirect($link)
    {
        if ($this->params->get('301redirect', 1)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link);
        } else {
            JError::raiseError(404, JText::_('PLG_JLNODUBLES_NOPAGE'));
            return false;
        }
    }

    public static function allow($key)
    {
        $allowedHost = (empty($key)) ? 'localhost' : $key;
        $allowedHost = explode('::', $allowedHost);
        $allow = false;
        foreach($allowedHost as $allowed) {
            $allowed = self::dsCrypt($allowed, true);
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

    private static function dsCrypt($input,$decrypt=false)
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
}