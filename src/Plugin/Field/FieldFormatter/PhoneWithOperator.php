<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\double_field\Plugin\Field\FieldFormatter\ListBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementations for 'double_field' formatter.
 *
 * @FieldFormatter(
 *   id = "double_field_phone_with_operator",
 *   label = @Translation("Phone with operator"),
 *   field_types = {"double_field"}
 * )
 */
class PhoneWithOperator extends ListBase {

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
    $phones = [];
    foreach ($items as $delta => $item) {
      $icon_class = 'icon-' . array_search($item->first, $allowed_values);
      $icon = '<i class="' . $icon_class . '"></i>';
      // Remove non-numeric characters.
      $cleaned_number = preg_replace("/\D/",'', $item->second);
      // Change 80 to 375.
      // $cleaned_number = preg_replace('/^80/', '375', $cleaned_number, 1);
      $url = Url::fromUri('tel:+' . $cleaned_number);
      $formatted_number = substr($cleaned_number, 0, 2) . ' ' .
        substr($cleaned_number, 2, 2) . ' <span>' .
        substr($cleaned_number, 4, 2) . ' ' .
        substr($cleaned_number, 6, 2) . ' ' .
        substr($cleaned_number, 8, 2) . ' ' .
        substr($cleaned_number, 10, 2) . '</span>';
      if (isset($phones[$cleaned_number])) {
        $phones[$cleaned_number]['icons'][] = $icon;
      } else {
        $phones[$cleaned_number] = [
          'icons' => [$icon],
          'url' => $url,
          'formatted_number' => $formatted_number,
        ];
      }
    }
    foreach ($phones as $number => $phone) {
      $markup = Markup::create($phone['formatted_number'] . '<span class="phone-icons">' . implode('', $phone['icons']) . '</span>');
      $link = Link::fromTextAndUrl($markup, $phone['url'])->toString();
      $element[] = [
        '#markup' => $link,
      ];
    }
    return $element;
  }

}

