<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Create API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_CreateTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
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
   * Test campagnodon create API. Must fail if no contribution.
   */
  public function testApiCreateWithoutContribution() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'create', array(
      'email' => 'bill.smith@example.com',
      'transaction_idx' => 'test/10'
    ));
  }

  /**
   * Test campagnodon create API. Must fail if no email provided.
   */
  public function testApiCreateWithoutEmail() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'create', array(
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
   * Test campagnodon create API. Must fail if no external transaction_idx.
   */
  public function testApiCreateWithoutTransactionIdx() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'create', array(
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
  public function testApiCreate($params) {
    $result = civicrm_api3('Campagnodon', 'create', $params);

    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);
    $contact = $result['values']['contact'];

    $this->assertEquals($params['email'], $contact['email']);
    $this->assertSame($params['first_name'] ?? '', $contact['first_name']);
    $this->assertSame($params['last_name'] ?? '', $contact['last_name']);
  }

  /**
   * @dataProvider dataTestInvalidProviders
   */
  public function testApiCreateInvalid($params) {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'create', $params);

    // should not be there...
    $this->assertTrue(false);
  }

  /**
   * Test campagnodon create API.
   * Tests deduplication of contacts.
   */
  public function testApiCreateDedup() {
    $result = civicrm_api3('Campagnodon', 'create', array(
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/30',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 12
        ]
      ]
    ));

    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);

    $contact_id = $result['values']['contact']['id'];
    $this->assertTrue(intval($contact_id) > 0);

    // Another donation, with a different contact.
    $result = civicrm_api3('Campagnodon', 'create', array(
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
    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);
    $this->assertTrue($contact_id != $result['values']['contact']['id']);

    $result = civicrm_api3('Campagnodon', 'create', array(
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
    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);
    $this->assertSame($contact_id, $result['values']['contact']['id']);
  }

}
