<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\colorbox\Plugin\Field\FieldFormatter\ColorboxFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'product_gallery_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "product_gallery_field_formatter",
 *   label = @Translation("Product gallery field formatter"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ProductGalleryFieldFormatter extends ColorboxFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['colorbox_node_style']['#title'] = $this->t('Small image style');
    $element['colorbox_node_style_first']['#title'] = $this->t('Big image style');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    // Collect cache tags to be added for each item in the field.
    $cache_tags = [];
    if (!empty($settings['colorbox_node_style']) && $settings['colorbox_node_style'] != 'hide') {
      $image_style = $this->imageStyleStorage->load($settings['colorbox_node_style']);
      $cache_tags = $image_style->getCacheTags();
    }
    $cache_tags_first = [];
    if (!empty($settings['colorbox_node_style_first'])) {
      $image_style_first = $this->imageStyleStorage->load($settings['colorbox_node_style_first']);
      $cache_tags_first = $image_style_first->getCacheTags();
    }
    $thumbs = [];
    foreach ($files as $delta => $file) {
      // Check if first image should have separate image style.
      $settings['style_first'] = FALSE;
      $settings['style_name'] = $settings['colorbox_node_style_first'];
      $cache_tags = Cache::mergeTags($cache_tags_first, $file->getCacheTags());

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item = $file->_referringItem;
      $item_attributes = $item->_attributes;
      unset($item->_attributes);

      $elements[$delta] = [
        '#theme' => 'colorbox_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#entity' => $items->getEntity(),
        '#settings' => $settings,
        '#cache' => [
          'tags' => $cache_tags,
        ],
      ];
      $thumbs[$delta] = [
        '#theme' => 'image_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#image_style' => $settings['colorbox_node_style'],
        '#cache' => [
          'tags' => $cache_tags,
        ],
      ];
    }

    // Attach the Colorbox JS and CSS.
    if ($this->attachment->isApplicable()) {
      $this->attachment->attach($elements);
    }

    return [
      '#theme' => 'product_gallery',
      'gallery' => $elements,
      'thumbs' => $thumbs,
    ];
  }


}
