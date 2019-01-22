<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\colorbox\ElementAttachmentInterface;
use Drupal\media\Entity\MediaType;
use Drupal\media\Plugin\media\Source\OEmbedInterface;

/**
 * Plugin implementation of the 'youtube_colorbox_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_colorbox_formatter",
 *   label = @Translation("Youtube colorbox formatter"),
 *   field_types = {
 *     "youtube",
 *     "link",
 *     "string",
 *     "string_long",
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
        'colorbox_gallery' => 'post',
        'colorbox_gallery_custom' => '',
        'attach_to' => '',
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

    $gallery = [
      'post' => $this->t('Per post gallery'),
      'page' => $this->t('Per page gallery'),
      'field_post' => $this->t('Per field in post gallery'),
      'field_page' => $this->t('Per field in page gallery'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('No gallery'),
    ];
    $elements['colorbox_gallery'] = [
      '#title' => $this->t('Gallery (image grouping)'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('colorbox_gallery'),
      '#options' => $gallery,
      '#description' => $this->t('How Colorbox should group the image galleries.'),
    ];
    $elements['colorbox_gallery_custom'] = [
      '#title' => $this->t('Custom gallery'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('colorbox_gallery_custom'),
      '#description' => $this->t('All images on a page with the same gallery value (rel attribute) will be grouped together. It must only contain lowercase letters, numbers, and underscores.'),
      '#required' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][colorbox_gallery]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $elements['colorbox_token_gallery'] = [
        '#type' => 'fieldset',
        '#title' => t('Replacement patterns'),
        '#theme' => 'token_tree_link',
        '#token_types' => [$form['#entity_type'], 'file'],
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][colorbox_gallery]"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }
    else {
      $elements['colorbox_token_gallery'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Replacement patterns'),
        '#description' => '<strong class="error">' . $this->t('For token support the <a href="@token_url">token module</a> must be installed.', ['@token_url' => 'http://drupal.org/project/token']) . '</strong>',
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][colorbox_gallery]"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }
    $elements['attach_to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attach to'),
      '#default_value' => $this->getSetting('attach_to'),
      '#description' => $this->t('Selector, that contains another colorbox gallery, to which video gallery should be attached.'),
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
    $gallery = [
      'post' => $this->t('Per post gallery'),
      'page' => $this->t('Per page gallery'),
      'field_post' => $this->t('Per field in post gallery'),
      'field_page' => $this->t('Per field in page gallery'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('No gallery'),
    ];
    if ($this->getSetting('colorbox_gallery')) {
      $summary[] = $this->t('Colorbox gallery type: @type', ['@type' => $gallery[$this->getSetting('colorbox_gallery')]]) . ($this->getSetting('colorbox_gallery') == 'custom' ? ' (' . $this->getSetting('colorbox_gallery_custom') . ')' : '');
    }
    if ($this->getSetting('attach_to')) {
      $summary[] = $this->t('Attach to: ') . $this->getSetting('attach_to');
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
        '#video_id' => $item->video_id ?: youtube_get_video_id($item->value),
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

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if ($field_definition->getType() === 'youtube') {
      return TRUE;
    }
    if ($field_definition->getTargetEntityTypeId() !== 'media') {
      return FALSE;
    }

    if (parent::isApplicable($field_definition)) {
      $media_type = $field_definition->getTargetBundle();

      if ($media_type) {
        $media_type = MediaType::load($media_type);
        return $media_type && $media_type->getSource() instanceof OEmbedInterface;
      }
    }
    return FALSE;
  }

}
