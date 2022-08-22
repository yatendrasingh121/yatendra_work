<?php

namespace Drupal\inline_all_css\Asset;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\inline_all_css\Event\CssPreRenderEvent;
use GuzzleHttp\ClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function file_get_contents;

/**
 * Decorates the CSS collection renderer service, adds Critical CSS.
 *
 * @see \Drupal\Core\Asset\CssCollectionRenderer
 */
class CriticalCssCollectionRenderer implements AssetCollectionRendererInterface {

  /**
   * The decorated collection renderer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionRendererInterface
   */
  protected $cssCollectionRenderer;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The filesystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The http client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a CriticalCssCollectionRenderer.
   *
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_collection_renderer
   *   The decorated asset renderer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger channel factory service.
   */
  public function __construct(AssetCollectionRendererInterface $css_collection_renderer, ConfigFactoryInterface $config_factory, ThemeManagerInterface $theme_manager, FileSystemInterface $file_system, EventDispatcherInterface $event_dispatcher, ClientInterface $http_client, LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->cssCollectionRenderer = $css_collection_renderer;
    $this->config = $config_factory->get('inline_all_css.settings');
    $this->themeManager = $theme_manager;
    $this->fileSystem = $file_system;
    $this->eventDispatcher = $event_dispatcher;
    $this->httpClient = $http_client;
    $this->logger = $logger_channel_factory->get('inline_all_css');
  }

  /**
   * Generates an inline style element from the provided assets.
   *
   * @param array $assets
   *   An asset collection.
   *
   * @return array
   *   An inline style tag element.
   */
  protected function getInlineCss(array $assets) {
    $css = '';
    foreach ($assets as $asset) {
      if ($asset['type'] === 'file') {
        $file = $this->fileSystem->realpath($asset['data']);
        $css .= file_get_contents($file);
      }
      elseif ($asset['type'] === 'external') {
        // Use guzzle to work around allow_url_fopen limitations.
        $resource = Url::fromUserInput($asset['data'], ['absolute' => TRUE])->toString();
        try {

          // Let's hope that the user is using the guzzle_cache module!
          $css .= $this->httpClient->request('GET', $resource)->getBody()->getContents();
        }
        catch (\Throwable $e) {
          $this->logger->warning($e->getMessage());
        }
      }
    }

    $event = new CssPreRenderEvent($css);
    /* @noinspection PhpMethodParametersCountMismatchInspection */
    $this->eventDispatcher->dispatch($event, CssPreRenderEvent::EVENT_NAME);
    $css = trim($event->getCss());

    return $css ? [
      '#type' => 'html_tag',
      '#tag' => 'style',
      '#value' => Markup::create($css),
    ] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $assets) {
    $elements = [];

    if ($this->config->get('enabled') === TRUE) {
      $enabled_themes = $this->config->get('enabled_themes');
      $active_theme = $this->themeManager->getActiveTheme()->getName();
      if (empty($enabled_themes) || in_array($active_theme, $enabled_themes, TRUE)) {
        $inline_css = $this->getInlineCss($assets);
        if ($inline_css) {
          $elements[] = $inline_css;
        }
        else {
          $elements = $this->cssCollectionRenderer->render($assets);
        }
      }
      else {
        $elements = $this->cssCollectionRenderer->render($assets);
      }
    }
    else {
      $elements = $this->cssCollectionRenderer->render($assets);
    }

    return $elements;
  }

}
