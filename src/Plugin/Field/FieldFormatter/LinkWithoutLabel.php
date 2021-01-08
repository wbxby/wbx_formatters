<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'EmptyLabelLinkFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "link_without_label",
 *   label = @Translation("LinkWithoutLabelFormatter"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkWithoutLabel extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'empty_label' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['empty_label'] = [
      '#type' => 'checkbox',
      '#title' => t('Without label'),
      '#default_value' => $this->getSetting('empty_label'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      // By default use the full URL as the link text.
      $url = $this->buildUrl($item);

      // If the checked empty_label field value is available, remove the link text.
      if (!empty($settings['empty_label'])) {
        $link_title = '';
      }
      else {
        $link_title = $url->toString();
      }

      $element[$delta] = [
        '#type' => 'link',
        '#title' => $link_title,
        '#options' => $url->getOptions(),
      ];
      $element[$delta]['#url'] = $url;

      if (!empty($item->_attributes)) {
        $element[$delta]['#options'] += ['attributes' => []];
        $element[$delta]['#options']['attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $element;
  }

}
