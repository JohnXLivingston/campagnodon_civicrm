<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Campaign API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_CampaignTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
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
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testCampaign() {
    civicrm_api3('Campaign', 'create', array(
      'name' => 'A campaign',
      'title' => 'The title',
      'goal_revenue' => 10000
    ));

    civicrm_api3('Campaign', 'create', array(
      'name' => 'Another campaign',
      'title' => 'The title 2'
    ));

    $expected = civicrm_api3('Campaign', 'get', array('sequential' => 0));
    foreach ($expected['values'] as &$c) {
      foreach ([
        'campaign_type_id', 'is_active',
        'created_id', 'created_date', 'last_modified_id', 'last_modified_date'
      ] as $field) {
        unset($c[$field]);
      }
      if (!empty($c['goal_revenue'])) {
        $c['current_revenue'] = 0;
      }
    }
    $result = civicrm_api3('Campagnodon', 'campaign', array('sequential' => 0));
    $this->assertEquals($expected, $result);
  }

  /**
   */
  public function testCampaignSequential() {
    civicrm_api3('Campaign', 'create', array(
      'name' => 'A campaign',
      'title' => 'The title',
      'goal_revenue' => 10000
    ));

    civicrm_api3('Campaign', 'create', array(
      'name' => 'Another campaign',
      'title' => 'The title 2'
    ));

    $expected = civicrm_api3('Campaign', 'get', array('sequential' => 1));
    foreach ($expected['values'] as &$c) {
      foreach ([
        'campaign_type_id', 'is_active',
        'created_id', 'created_date', 'last_modified_id', 'last_modified_date'
      ] as $field) {
        unset($c[$field]);
      }
      if (!empty($c['goal_revenue'])) {
        $c['current_revenue'] = 0;
      }
    }
    $result = civicrm_api3('Campagnodon', 'campaign', array('sequential' => 1));
    $this->assertEquals($expected, $result);
  }
}

// TODO: test with a current_revenue
