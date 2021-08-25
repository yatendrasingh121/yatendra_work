<?php

namespace Drupal\donation_mode\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'DonationModeFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "donation_mode_formatter",
 *   label = @Translation("Donation Mode"),
 *   field_types = {
 *     "donation_mode"
 *   }
 * )
 */
class DonationModeFormatter extends FormatterBase {

  /**
   * Define how the field type is showed.
   *
   * Inside this method we can customize how the field is displayed inside
   * pages.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => $item->mode . ', ' . $item->medium,
      ];
    }

    return $elements;
  }

}
