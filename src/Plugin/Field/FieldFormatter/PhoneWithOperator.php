<?php

namespace Drupal\wbx_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\double_field\Plugin\Field\FieldFormatter\ListBase;
use Drupal\Core\Form\FormStateInterface;
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
  public static function defaultSettings() {
    return ['group' => TRUE] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $settings = $this->getSettings();

    $element['group'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Group messengers by phone'),
      '#default_value' => $settings['group'],
      '#weight' => -15,
    ];

    return $element;
  }

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
    $group = $this->getSetting('group');
    foreach ($items as $delta => $item) {
      $icon_class = 'icon-' . array_search($item->first, $allowed_values);
      $icon = '<i class="' . $icon_class . '"></i>';
      // Remove non-numeric characters.
      $cleaned_number = preg_replace("/\D/",'', $item->second);
      // Change 80 to 375.
      // $cleaned_number = preg_replace('/^80/', '375', $cleaned_number, 1);
      $url = Url::fromUri('tel:+' . $cleaned_number);
      $formatted_number = '+' . substr($cleaned_number, 0, 3) . ' (' .
        substr($cleaned_number, 3, 2) . ') <span>' .
        substr($cleaned_number, 5, 3) . ' ' .
        substr($cleaned_number, 8, 2) . ' ' .
        substr($cleaned_number, 10, 2) . '</span>';
      $key = $group ? $cleaned_number : $delta;
      if (isset($phones[$key])) {
        $phones[$key]['icons'][] = $icon;
      } else {
        $phones[$key] = [
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

