<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test\CiviEnvBuilder;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class Civi_Api4_CampagnodonTransaction_Test extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  // use \Civi\Test\ContactTestTrait;
  // use \Civi\Test\Api3TestTrait;
  // use Civi\Test\ACLPermissionTrait;

  /**
   * Setup used when HeadlessInterface is implemented.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * @link https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
   *
   * @return \Civi\Test\CiviEnvBuilder
   *
   * @throws \CRM_Extension_Exception_ParseException
   */
  public function setUpHeadless(): CiviEnvBuilder {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp():void {
    parent::setUp();
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access Campagnodon'];
  }

  public function tearDown():void {
    parent::tearDown();
  }

  /**
   * Tests permissions.
   */
  public function testPermissions():void {
    // $this->createLoggedInUser();
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access Campagnodon', 'administer CiviCRM'];
    $result = civicrm_api3('Campagnodon', 'start', array(
      'check_permissions' => 0,
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/1',
      'country' => 'FR',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 12,
          'currency' => 'EUR'
        ]
      ]
    ));
    $this->assertEquals(1, $result['count'], 'Must have created 1 transaction');
    $result = \Civi\Api4\CampagnodonTransaction::get()->setCheckPermissions(false)->addSelect('*')->selectRowCount()->execute();
    $this->assertEquals(1, $result->count(), 'Must have one transaction in DB');
    $result = \Civi\Api4\CampagnodonTransactionLink::get()->setCheckPermissions(false)->addSelect('*')->selectRowCount()->execute();
    $this->assertTrue($result->count() > 0, 'Must have transaction links in DB');

    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access Campagnodon', 'administer CiviCRM'];
    $result = \Civi\Api4\CampagnodonTransaction::get()->setCheckPermissions(true)->addSelect('*')->selectRowCount()->execute();
    $this->assertEquals(1, $result->count(), 'Must found one transaction (with administer rights)');
    $result = \Civi\Api4\CampagnodonTransactionLink::get()->addSelect('*')->selectRowCount()->execute();
    $this->assertTrue($result->count() > 0, 'Must have transaction links in DB (with administer rights)');

    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access Campagnodon'];
    $result = \Civi\Api4\CampagnodonTransaction::get()->setCheckPermissions(true)->addSelect('*')->selectRowCount()->execute();
    $this->assertEquals(1, $result->count(), 'Must found one transaction');
    $result = \Civi\Api4\CampagnodonTransactionLink::get()->addSelect('*')->selectRowCount()->execute();
    $this->assertTrue($result->count() > 0, 'Must have transaction links in DB');

    $this->expectException(\Civi\API\Exception\UnauthorizedException::class);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM'];
    $result = \Civi\Api4\CampagnodonTransaction::get()->setCheckPermissions(true)->addSelect('*')->selectRowCount()->execute();
    // $this->assertEquals(0, $result->count(), 'Must not found any transaction');
  }

}
