<?php

namespace Drupal\donation_mode\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'DonationModeWidget' widget.
 *
 * @FieldWidget(
 *   id = "donation_mode_widget",
 *   label = @Translation("Select Donation Mode"),
 *   field_types = {
 *     "donation_mode"
 *   }
 * )
 */
class DonationModeWidget extends WidgetBase {

  /**
   * Define the form for the field type.
   *
   * Inside this method we can define the form used to edit the field type.
   *
   * Here there is a list of allowed element types: https://goo.gl/XVd4tA
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    Array $element,
    Array &$form,
    FormStateInterface $formState
  ) {
    // Donation Mode and medium Fields.
    $element['mode'] = [
      '#type' => 'select',
      '#title' => t('Donation Mode'),
      // Set here the current value for this field, or a default value (or
      // null) if there is no a value.
      '#default_value' => isset($items[$delta]->mode) ?
      $items[$delta]->mode : NULL,
      '#empty_value' => '',
      '#options' => ['online' => 'Online', 'offline' => 'Offline'],
    ];
    $field_name = $items->getName();
    $element['medium'] = [
      '#type' => 'radios',
      '#title' => t('Donation Medium'),
      '#default_value' => isset($items[$delta]->medium) ?
      $items[$delta]->medium : NULL,
      '#empty_value' => '',
      '#options' => ['cash' => 'Cash', 'cheque' => 'Cheque'],
      '#states' => [
        'visible' => [
          ':input[name="' . $field_name . '[' . $delta . '][mode]"]' => ['value' => 'offline'],
        ],
        'required' => [
          ':input[name="' . $field_name . '[' . $delta . '][mode]"]' => ['value' => 'offline'],
        ],
      ],
    ];
    return $element;
  }

}
