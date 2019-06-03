<?php

namespace Drupal\custom_admin_mods\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change the core's default path for `/contact` so that we can use it as an alias.
    if ($route = $collection->get('contact.site_page')) {
      $route->setPath('/contactform');
    }
    if ($route = $collection->get('entity.contact_form.canonical')) {
      $route->setPath('/contactform/{contact_form}');
    }
  }
}