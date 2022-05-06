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
   * Provide contacts of unit tests.
   */
  public function dataTestProviders() {
    return [
      'john' => [array(
        'email' => 'john.doe@example.com',
        'donation_amount' => 12
      )],
      'bill' => [array(
        'email' => 'bill.smith@example.com',
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'donation_amount' => 45
      )]
    ];
  }

  /**
   * Test campagnodon create API. Must fail if no email provided.
   */
  public function testApiCreateWithoutEmail() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'create', array());
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
   * Test campagnodon create API.
   * Tests deduplication of contacts.
   */
  public function testApiCreateDedup() {
    $result = civicrm_api3('Campagnodon', 'create', array(
      'email' => 'john.doe@example.com',
      'donation_amount' => 12
    ));

    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);

    $contact_id = $result['values']['contact']['id'];
    $this->assertTrue(intval($contact_id) > 0);

    // Another donation, with a different contact.
    $result = civicrm_api3('Campagnodon', 'create', array(
      'email' => 'bill.smith@example.com',
      'first_name' => 'Bill',
      'last_name' => 'Smith',
      'donation_amount' => 34
    ));
    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);
    $this->assertTrue($contact_id != $result['values']['contact']['id']);

    $result = civicrm_api3('Campagnodon', 'create', array(
      'email' => 'john.doe@example.com',
      'first_name' => 'John',
      'last_name' => 'Doe',
      'donation_amount' => 15
    ));
    $this->assertEquals(1, $result['count']);
    $this->assertArrayHasKey('contact', $result['values']);
    $this->assertSame($contact_id, $result['values']['contact']['id']);
  }

}
