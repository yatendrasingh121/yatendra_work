<?php

namespace Drupal\axe_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PageJsonController.
 *
 * @package Drupal\axe_test\Controller
 */
class PageJsonController extends ControllerBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

  /**
   * Callback for returning page json.
   *
   * @param string $site_api_key
   *   Api key has been passed in argument.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node that has been passed in argument.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   */
  public function getJson($site_api_key, NodeInterface $node) {
    $data['status_code'] = 1;
    $data['status_message'] = '';
    $data['method'] = 'GET';
    $result[] = [
      'id' => $node->id(),
      'title' => $node->getTitle(),
    ];
    $data['data'] = $result;
    return new JsonResponse($data);
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @param string $site_api_key
   *   Run access checks for this api key.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Run access checks for node.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess(AccountInterface $account, $site_api_key, NodeInterface $node) {
    $api_key = $this->configFactory->get('system.site')->get('siteapikey');
    if (!empty($node) && $node->getType() == 'page' && $site_api_key == $api_key) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
