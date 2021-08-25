<?php

namespace Drupal\donation_mode\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface as StorageDefinition;

/**
 * Plugin implementation of the 'Donation Form' field type.
 *
 * @FieldType(
 *   id = "donation_mode",
 *   label = @Translation("Donation Mode"),
 *   description = @Translation("Creates Donation Mode Field Type."),
 *   category = @Translation("Custom"),
 *   default_widget = "donation_mode_widget",
 *   default_formatter = "donation_mode_formatter"
 * )
 */
class DonationMode extends FieldItemBase {

  /**
   * Field type properties definition.
   *
   * Inside this method we defines all the fieofflinelds (properties) that our
   * custom field type will have.
   *
   * Here there is a list of allowed property types: https://goo.gl/sIBBgO
   */
  public static function propertyDefinitions(StorageDefinition $storage) {

    $properties = [];

    $properties['mode'] = DataDefinition::create('string')
      ->setLabel(t('Mode'));

    $properties['medium'] = DataDefinition::create('string')
      ->setLabel(t('Medium'));

    return $properties;
  }

  /**
   * Field type schema definition.
   *
   * Inside this method we defines the database schema used to store data for
   * our field type.
   *
   * Here there is a list of allowed column types: https://goo.gl/YY3G7s
   */
  public static function schema(StorageDefinition $storage) {

    $columns = [];
    $columns['mode'] = [
      'type' => 'varchar',
      'length' => 25,
    ];
    $columns['medium'] = [
      'type' => 'varchar',
      'length' => 25,
    ];

    return [
      'columns' => $columns,
      'indexes' => [],
    ];
  }

  /**
   * Define when the field type is empty.
   *
   * This method is important and used internally by Drupal. Take a moment
   * to define when the field fype must be considered empty.
   */
  public function isEmpty() {

    $isEmpty =
      empty($this->get('mode')->getValue()) &&
      empty($this->get('medium')->getValue());

    return $isEmpty;
  }

}
