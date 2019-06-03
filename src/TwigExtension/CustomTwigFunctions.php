<?php

namespace Drupal\custom_admin_mods\TwigExtension;

// Include anything we need to use down below
use \Drupal\Component\Utility\Html;
use \Drupal\Core\Entity;
use \Drupal\Core\Entity\EntityTypeManager;
use \Drupal\Core\Menu\MenuTreeParameters;
use \Drupal\Core\Url;
use \Drupal\menu_link_content\Entity\MenuLinkContent;
use \EntityFieldQuery;

class CustomTwigFunctions extends \Twig_Extension {

  /**
   * Generates a list of all Twig functions that this extension defines.
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('to_array', array($this, 'toArray')),
    );
  }

  /**
   * Generates a list of all Twig functions that this extension defines.
   */
  public function getFunctions() {
    return array(
      'urlFromUri' => new
        \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'urlFromUri')
      ),
      'getUrlAlias' => new
        \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'getUrlAlias')
      ),
      'getEntity' => new
        \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'getEntity')
	  ),
	  'getTrimmed' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'getTrimmed')
	  ),
	  'cleanText' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'cleanText')
	  ),
	  'dateFormat' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'dateFormat')
	  ),
	  'br2nl' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'br2nl')
	  ),
	  'trim_preview' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'trim_preview')
	  ),
	  'getHero' => new
	    \Twig_Function_Function(array('Drupal\custom_admin_mods\TwigExtension\CustomTwigFunctions', 'getHero')
	  ),
    );
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'custom_admin_mods.twig_extension';
  }

	public static function dateFormat($field,$format = 'l, F jS g:ia') {
		$date = $field->date;
		$formatted = \Drupal::service('date.formatter')->format($date->getTimestamp(), 'custom', $format);
		return $formatted;
	}

  public static function getUrlAlias($nid) {
    return \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$nid);
  }

  public static function br2nl($content) {
  	return preg_replace('#<br\s*/?>#i', "\n", $content);
  }

  public static function urlFromUri($uri) {
    return Url::fromUri($uri);
  }

	public static function getHero($nid) {
		$storage = \Drupal::entityTypeManager()->getStorage('node');
		$node = $storage->load($nid);
		$entities = $node->get('field_content')->referencedEntities();
		foreach($entities as $key => $paragraph) {
			if($paragraph->getType() == "hero") {
				$heroes = file_create_url($paragraph->get('field_image')->entity->uri->value);
			}
		}
		return $heroes;
	}

	public static function getEntity($nid,$entity_type = "node", $view_mode = 'full') {

		$view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
		$storage = \Drupal::entityTypeManager()->getStorage($entity_type);
		$node = $storage->load($nid);
		$build = $view_builder->view($node, $view_mode);
		return render($build);

	}

	public static function trim_preview($string, $count=2) {
		// $count represents the # of elements to return from the passed string of HTML
		// 2 by default, the lead headline and the first paragraph as an example

    $return = '';
    $doc = \Drupal\Component\Utility\Html::load($string);
    $xpath = new \DOMXPath($doc);

    // Remove comment nodes
    foreach ($xpath->query('//comment()') as $comment) {
      $comment->parentNode->removeChild($comment);
    }

    $doc->normalize();

    // Remove empty nodes
    while (($nodes = $xpath->query('//*[not(*) and not(@*) and not(text()[normalize-space()])]')) && $nodes->length) {
      foreach ($nodes as $node) {
        $node->parentNode->removeChild($node);
      }
    }

    $body = $doc->getElementsByTagName('body');

    $c = 1;
    if ($body && $body->length > 0) {
      foreach ($body as $b) {
    		foreach ($b->childNodes as $child) {
    		  if ($c <= $count) {
      		  // Capture output of nodes we want to keep.
      			$return .= $doc->saveHTML($child);
      		}

      		if ($child->nodeType != XML_TEXT_NODE || trim($child->textContent) != '') {
        		$c++;
      		}
    		}
      }
    }

		return $return;
	}

  public static function toArray($obj) {
    return (array)$obj;
  }

  public static function getTrimmed($string, $length = 100) {
		$string = strip_tags($string);
		if(mb_strlen($string) > $length) {
			$string = substr($string, 0, $length) . '...';
		} else {
			$string = substr($string, 0, $length);
		}
		return $string;
	}
  public static function cleanText($string, $length = 100) {
		// clean text
		$string = strip_tags($string);
		$string = html_entity_decode($string);
		$string = self::getTrimmed($string, $length);
		return $string;
	}

}
