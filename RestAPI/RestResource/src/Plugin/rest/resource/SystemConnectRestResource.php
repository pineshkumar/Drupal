<?php

namespace Drupal\rest_resources\Plugin\rest\resource;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get system connect status.
 *
 * @RestResource(
 *   id = "system_connect_rest_resource",
 *   label = @Translation("System connect"),
 *   uri_paths = {
 *     "canonical" = "/system/connect"
 *   }
 * )
 */
class SystemConnectRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Constructs a new CreateAccountSplitterRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   The CSRF token generator.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    CsrfTokenGenerator $csrf_token,
    RouteProviderInterface $route_provider
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->csrfToken = $csrf_token;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_resources'),
      $container->get('current_user'),
      $container->get('csrf_token'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    $logout_route = $this->routeProvider->getRouteByName('user.logout.http');
    $logout_path = ltrim($logout_route->getPath(), '/');

    $data = [
      'current_user' => [
        'uid' => $this->currentUser->id(),
        'roles' => $this->currentUser->getRoles(),
        'name' => $this->currentUser->getAccountName(),
      ],
      'csrf_token' => $this->csrfToken->get('rest'),
      'logout_token' => $this->csrfToken->get($logout_path),
    ];

    return new ModifiedResourceResponse($data);
  }

  /**
   * {@inheritdoc}
   */
  public function permissions() {
    return [];
  }

}
