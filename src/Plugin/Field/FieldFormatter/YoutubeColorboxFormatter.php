<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\colorbox\ElementAttachmentInterface;

/**
 * Plugin implementation of the 'youtube_colorbox_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_colorbox_formatter",
 *   label = @Translation("Youtube colorbox formatter"),
 *   field_types = {
 *     "youtube"
 *   }
 * )
 */
class YoutubeColorboxFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  protected $attachment;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\colorbox\ElementAttachmentInterface $attachment
   *   Allow the library to be attached to the page.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ElementAttachmentInterface $attachment) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->attachment = $attachment;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('colorbox.attachment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'image_style' => 'thumbnail',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['image_style'] = [
      '#type' => 'select',
      '#title' => t('Image style'),
      '#options' => image_style_options(FALSE),
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $image_style = $this->getSetting('image_style');
    $image_link = $this->getSetting('image_link');

    if ($image_style) {
      $summary[] = t('Image style: @style_name.', ['@style_name' => $image_style]);
    }
    if ($image_link) {
      $summary[] = t('Linked to: @image_link.', ['@image_link' => $image_link]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {

      $element[$delta] = [
        '#theme' => 'youtube_colorbox',
        '#video_id' => $item->video_id,
        '#entity_title' => $items->getEntity()->label(),
        '#image_style' => $this->getSetting('image_style'),
        '#settings' => $this->getSettings(),
        '#entity' => $items->getEntity(),
        '#item' => $item,
      ];
    }

    // Attach the Colorbox JS and CSS.
    if ($this->attachment->isApplicable()) {
      $this->attachment->attach($element);
    }
    $element['#attached']['library'][] = 'wbx_formatters/colorbox-youtube-init';
    $element['#attached']['drupalSettings']['colorboxYoutube'] = $element['#attached']['drupalSettings']['colorbox'];
    unset($element['#attached']['drupalSettings']['colorbox']);
    $element['#attached']['drupalSettings']['colorboxYoutube']['iframe'] = TRUE;
    $element['#attached']['drupalSettings']['colorboxYoutube'] += [
      'width' => 640,
      'height' => 480,
    ];

    return $element;
  }

}
