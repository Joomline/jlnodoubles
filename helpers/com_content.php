<?php
/**
 * @package plg_jlnodubles
 * @author Arkadiy (a.sedelnikov@gmail.com), Vadim Kunicin (vadim@joomline.ru), Sher ZA (irina@hekima.ru).
 * @version 1.1
 * @copyright (C) 2014 by JoomLine (http://www.joomline.net)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */
defined( '_JEXEC' ) or die;
use Joomla\Registry\Registry;

require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

class JLNodoubles_com_content_helper extends JLNodoublesHelper {

	function __construct( $params ) {
		parent::__construct( $params );
	}

	public function go( $allGet ) {
		$original_link = '';
		$app           = JFactory::getApplication();
		$uri           = JUri::getInstance();
		$homealias     = $this->params->get( 'homealias', 'home' );
		$currentLink   = $uri->toString( array( 'path', 'query' ) );

		switch ( $allGet['view'] ) {
			case 'article':
				$db    = JFactory::getDbo();
				$query = $db->getQuery( true );
				$query->select( '`id`, `alias`, `catid`, `language`' )
				      ->from( '#__content' )
				      ->where( '`id` = ' . (int) $allGet['id'] );
				$item = $db->setQuery( $query, 0, 1 )->loadObject();

				if ( is_null( $item ) ) {
					return true;
				}

				$menu      = $app->getMenu();
				$FixItemid = $menu->getActive()->id;



				for ( $i = 0; $i < 6; $i ++ )
				{
					$itemsfix = $this->params->get( 'itemsfix' . $i );
					if ( !empty($itemsfix) )
					{
						$itemsfix_array = explode( ",", $itemsfix );
						$itemIdfix      = $this->params->get( 'itemId' . $i );
						if ( in_array( $item->id, $itemsfix_array ) )
						{
							$FixItemid = $itemIdfix;
						}
					}
				}

				$original_link = JRoute::_( "index.php?option=com_content&view=article&id={$item->id}:{$item->alias}&catid={$item->catid}&Itemid={$FixItemid}" );

				if ( ! $original_link ) {
					return true;
				}

				if ( strpos( $original_link, 'component/content/article' ) !== false && ! empty( $homealias ) ) {
					$original_link = str_replace( 'component/content/article', $homealias, $original_link );
				}

				$symb = "?";

				if ( $app->input->getInt( 'start' ) > 0 ) {
					$original_link .= $symb . "start=" . $app->input->getInt( 'start' );
					$symb          = "&";
				}

				if ( $app->input->getInt( 'showall' ) > 0 ) {
					$original_link .= $symb . "showall=" . $app->input->getInt( 'showall' );
				}
				break;

			case 'frontpage':
			case 'featured':

				if(JUri::root() == JUri::current()){
					return true;
				}

				$link = 'index.php?option=com_content&view=' . $allGet['view'];
				if ( $app->input->getInt( 'start' ) > 0 ) {
					$link .= '&start=' . $app->input->getInt( 'start' );
				}
				$original_link = JRoute::_( $link );
				break;

			case 'category':
				$original_link = JRoute::_( ContentHelperRoute::getCategoryRoute( $allGet['id'] ), false );

				$start = $app->input->getInt( 'start', 0 );
				if ( $start > 0 ) {
					$params     = $app->getParams();
					$menuParams = new Registry;

					if ( $menu = $app->getMenu()->getActive() ) {
						$menuParams->loadString( $menu->params );
					}

					$mergedParams = clone $menuParams;
					$mergedParams->merge( $params );
					$itemid = $app->input->get( 'id', 0, 'int' ) . ':' . $app->input->get( 'Itemid', 0, 'int' );

					if ( ( $app->input->get( 'layout' ) == 'blog' ) || $params->get( 'layout_type' ) == 'blog' ) {
						$limit = $params->get( 'num_leading_articles' ) + $params->get( 'num_intro_articles' );
					} else {
						$limit = $app->getUserStateFromRequest( 'com_content.category.list.' . $itemid . '.limit', 'limit', $params->get( 'display_num' ), 'uint' );
					}

					if ( $start % $limit != 0 ) {
						$start = intval( $start / $limit ) * $limit;
					}
					$original_link .= "?start=" . $start;
				}
				break;
			case 'form':
				return true;
				break;
			default:
				return false;
				break;
		}

		if ( $original_link && ( $this->urlEncode( $original_link ) != $currentLink ) ) {
			$this->shRedirect( $original_link );
		}

		return true;
	}
}
