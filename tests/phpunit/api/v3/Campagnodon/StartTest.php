<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Start API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_StartTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
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
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Provide valid contacts for unit tests.
   */
  public function dataTestProviders() {
    // TODO: add some tests with a campaign_id
    return [
      'john' => [array(
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 12
          ]
        ]
      )],
      'bill' => [array(
        'email' => 'bill.smith@example.com',
        'transaction_idx' => 'test/123456789123456789',
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45
          ]
        ]
      )]
    ];
  }

  /**
   * Provide invalid contacts for unit tests.
   */
  public function dataTestInvalidProviders() {
    return [
      'must fail because of invalid contribution amount' => [array(
        'email' => 'michel.martin@example.com',
        'transaction_idx' => 'test/2',
        'first_name' => 'Michel',
        'last_name' => 'Martin',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 'nonono'
          ]
        ]
      )],
      'must fail because of unknown campaign' => [array(
        'email' => 'john.doe@example.com',
        'campaign_id' => '123456749',
        'transaction_idx' => 'test/3',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 12
          ]
        ]
      )]
    ];
  }

  /**
   * Test campagnodon start API. Must fail if no contribution.
   */
  public function testApiStartWithoutContribution() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'start', array(
      'email' => 'bill.smith@example.com',
      'transaction_idx' => 'test/10'
    ));
  }

  /**
   * Test campagnodon Start API. Must fail if no email provided.
   */
  public function testApiStartWithoutEmail() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'start', array(
      'transaction_idx' => 'test/20',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45
        ]
      ]
    ));
  }

  /**
   * Test campagnodon Start API. Must fail if no external transaction_idx.
   */
  public function testApiStartWithoutTransactionIdx() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'start', array(
      'email' => 'bill.smith@example.com',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45
        ]
      ]
    ));
  }


  /**
   * @dataProvider dataTestProviders
   */
  public function testApiStart($params) {
    $result = civicrm_api3('Campagnodon', 'start', $params);

    $this->assertEquals(1, $result['count'], 'Must have 1 result');
    $this->assertEquals(1, count($result['values']), 'Must have one value');

    $transaction = array_pop($result['values']);

    $this->assertTrue(intval($transaction['id']) > 0, 'Must have a transaction id');
    $this->assertTrue(intval($transaction['contact_id']) > 0, 'Must have a contact id');
    $this->assertEquals($params['email'], $transaction['email'], 'Field email must have the correct value');
    $this->assertSame($params['first_name'] ?? '', $transaction['first_name'] ?? '', 'Field first_name must have the correct value');
    $this->assertSame($params['last_name'] ?? '', $transaction['last_name'] ?? '', 'Field last_name must have the correct value');
  }

  /**
   * @dataProvider dataTestInvalidProviders
   */
  public function testApiStartInvalid($params) {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'start', $params);

    // should not be there...
    $this->assertTrue(false);
  }

  /**
   * Test campagnodon Start API.
   * Tests deduplication of contacts.
   */
  public function testApiStartDedup() {
    $result = civicrm_api3('Campagnodon', 'start', array(
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/30',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 12
        ]
      ]
    ));

    $this->assertEquals(1, $result['count'], 'Must have 1 result');
    $transaction1 = array_pop($result['values']);

    $contact_id = $transaction1['contact_id'];
    $this->assertTrue(intval($contact_id) > 0, 'Must have a contact_id');

    // Another donation, with a different contact.
    $result = civicrm_api3('Campagnodon', 'start', array(
      'email' => 'bill.smith@example.com',
      'transaction_idx' => 'test/31',
      'first_name' => 'Bill',
      'last_name' => 'Smith',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 34
        ]
      ]
    ));
    $this->assertEquals(1, $result['count'], 'Second start must have 1 result');
    $transaction2 = array_pop($result['values']);
    $this->assertTrue($contact_id != $transaction2['contact_id'], 'Second start must have created a new contact');

    $result = civicrm_api3('Campagnodon', 'start', array(
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/32',
      'first_name' => 'John',
      'last_name' => 'Doe',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 15
        ]
      ]
    ));
    $this->assertEquals(1, $result['count'], 'Third start must have 1 result');
    $transaction3 = array_pop($result['values']);
    $this->assertSame($contact_id, $transaction3['contact_id'], 'Third start must have reused the first contact');
  }

}
