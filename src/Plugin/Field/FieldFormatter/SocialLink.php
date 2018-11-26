<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\double_field\Plugin\Field\FieldFormatter\ListBase;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementations for 'double_field' formatter.
 *
 * @FieldFormatter(
 *   id = "double_field_social_link",
 *   label = @Translation("Social Link"),
 *   field_types = {"double_field"}
 * )
 */
class SocialLink extends ListBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $anyItem = reset($items);
    $anyItem = reset($anyItem);
    $definition = $anyItem->getFieldDefinition();
    $item_settings = $definition->get('settings');
    $allowed_values = $item_settings['first']['allowed_values'];
    foreach ($items as $delta => $item) {
      $icon_class = 'icon-' . array_search($item->first, $allowed_values);
      $icon = Markup::create('<i class="' . $icon_class . '"></i>');
      $url = $item->second['#url'];
      $url->setOptions([
        'attributes' => [
          'target' => '_blank',
          'rel' => 'noreferrer',
          'aria-label' => $item->first,
        ]
      ]);
      $link = Link::fromTextAndUrl($icon, $url)->toString();
      $element[$delta] = [
        '#markup' => $link,
      ];
    }
    return $element;
  }

}
