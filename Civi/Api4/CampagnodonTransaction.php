<?php
namespace Civi\Api4;

/**
 * CampagnodonTransaction entity.
 *
 * Provided by the Campagnodon extension.
 *
 * @package Civi\Api4
 */
class CampagnodonTransaction extends Generic\DAOEntity {
  public static function permissions() {
    return [
      'meta' => ['access CiviCRM'],
      'default' => ['access CiviCRM', 'access Campagnodon'] // FIXME: test if it works.
    ];
  }
}
