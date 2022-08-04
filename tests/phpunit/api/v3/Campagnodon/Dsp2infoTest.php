<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Dsp2info API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_Dsp2infoTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply(false);
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    parent::setUp();

    civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/fulldata',
      'operation_type' => 'donation',
      'country' => 'FR',
      'last_name' => 'Doe',
      'first_name' => 'John',
      'street_address' => '13 bourbon street',
      'supplemental_address_1' => 'sup 1',
      'supplemental_address_2' => 'sup 2',
      'postal_code' => '13120',
      'city' => 'London',
      'country' => 'GB',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45,
          'currency' => 'EUR'
        ]
      ]
    ));
    civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'john.doe2@example.com',
      'transaction_idx' => 'test/minimaldata',
      'operation_type' => 'donation',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45,
          'currency' => 'EUR'
        ]
      ]
    ));

    // Set permissions to 'Campagnodon api' only.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['Campagnodon api'];
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Check that we dont have any permission other than «Campagnodon api».
   */
  public function testNoApi3Permissions() {
    $result = civicrm_api3('Contact', 'get', array('check_permissions' => 1));
    $this->assertEquals(0, count($result['values']), 'Must have no value');

    $this->expectException(CiviCRM_API3_Exception::class);
    civicrm_api3('Contact', 'create', array('check_permissions' => 1, 'email' => 'john.doe.no@example.com'));
  }

  /**
   * Check that we dont have any permission other than «Campagnodon api».
   */
  public function testNoApi4Permissions() {
    $result = \Civi\Api4\Contact::get()->setCheckPermissions(true)->selectRowCount()->execute();
    $this->assertEquals(0, $result->count(), 'Must have no value');

    $this->expectException(\Civi\API\Exception\UnauthorizedException::class);
    $result = \Civi\Api4\Contact::create()
      ->setCheckPermissions(true)
      ->addValue('email', 'john.doe.no@example.com')
      ->execute();
  }

  /**
   * Test with full data
   */
  public function testFullData() {
    $result = civicrm_api3('Campagnodon', 'dsp2info', array(
      'transaction_idx' => 'test/fulldata',
      'sequential' => true
    ));
    $this->assertEquals(1, $result['count'], 'Exactly 1 result');
    $this->assertEquals('john.doe@example.com', $result['values'][0]['email'], 'Field email');
    $this->assertEquals('John', $result['values'][0]['first_name'], 'Field first_name');
    $this->assertEquals('Doe', $result['values'][0]['last_name'], 'Field last_name');
    $this->assertEquals('GB', $result['values'][0]['country'], 'Field country');
    $this->assertEquals('13 bourbon street', $result['values'][0]['street_address'], 'Field street_address');
    $this->assertEquals('sup 1', $result['values'][0]['supplemental_address_1'], 'Field supplemental_address_1');
    $this->assertEquals('sup 2', $result['values'][0]['supplemental_address_2'], 'Field supplemental_address_2');
    $this->assertEquals('13120', $result['values'][0]['postal_code'], 'Field postal_code');
    $this->assertEquals('London', $result['values'][0]['city'], 'Field city');
  }

  public function testStatusPending() {
    civicrm_api3('Campagnodon', 'updatestatus', array(
      'transaction_idx' => 'test/fulldata',
      'status' => 'pending'
    ));

    $result = civicrm_api3('Campagnodon', 'dsp2info', array(
      'transaction_idx' => 'test/fulldata',
      'sequential' => true
    ));
    $this->assertEquals(1, $result['count'], 'Exactly 1 result');
    $this->assertEquals('john.doe@example.com', $result['values'][0]['email'], 'Field email');
    $this->assertEquals('John', $result['values'][0]['first_name'], 'Field first_name');
    $this->assertEquals('Doe', $result['values'][0]['last_name'], 'Field last_name');
    $this->assertEquals('GB', $result['values'][0]['country'], 'Field country');
    $this->assertEquals('13 bourbon street', $result['values'][0]['street_address'], 'Field street_address');
    $this->assertEquals('13120', $result['values'][0]['postal_code'], 'Field postal_code');
    $this->assertEquals('London', $result['values'][0]['city'], 'Field city');
  }

  public function dataTestInvalidStatus() {
    return [
      'must fail when status is completed' => ['completed'],
      'must fail when status is double_membership' => ['double_membership'],
      'must fail when status is cancelled' => ['cancelled'],
      'must fail when status is failed' => ['failed'],
      'must fail when status is refunded' => ['refunded']
    ];
  }
  /**
   * @dataProvider dataTestInvalidStatus
   */
  public function testInvalidStatus($status) {
    civicrm_api3('Campagnodon', 'updatestatus', array(
      'transaction_idx' => 'test/fulldata',
      'status' => $status
    ));

    $this->expectException(CiviCRM_API3_Exception::class);

    $result = civicrm_api3('Campagnodon', 'dsp2info', array(
      'transaction_idx' => 'test/fulldata',
      'sequential' => true
    ));
  }

  public function testWrongIdx() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'dsp2info', array(
      'transaction_idx' => 'test/nothing_to_see',
      'sequential' => true
    ));
  }

  public function testMinimalData() {
    $result = civicrm_api3('Campagnodon', 'dsp2info', array(
      'transaction_idx' => 'test/minimaldata',
      'sequential' => true
    ));
    $this->assertEquals(1, $result['count'], 'Exactly 1 result');
    $this->assertEquals('john.doe2@example.com', $result['values'][0]['email'], 'Field email');
    $this->assertEquals(null, $result['values'][0]['first_name'], 'Field first_name');
    $this->assertEquals(null, $result['values'][0]['last_name'], 'Field last_name');
    $this->assertEquals(null, $result['values'][0]['country'], 'Field country');
    $this->assertEquals(null, $result['values'][0]['street_address'], 'Field street_address');
    $this->assertEquals(null, $result['values'][0]['supplemental_address_1'], 'Field supplemental_address_1');
    $this->assertEquals(null, $result['values'][0]['supplemental_address_2'], 'Field supplemental_address_2');
    $this->assertEquals(null, $result['values'][0]['postal_code'], 'Field postal_code');
    $this->assertEquals(null, $result['values'][0]['city'], 'Field city');
  }

}
