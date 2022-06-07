<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Updatestatus API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_UpdatestatusTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  private $idx_cpt = 1;

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
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  private function getSimpleStartParams($idx) {
    return [
      'email' => 'john.doe@example.com',
      'transaction_idx' => $idx,
      'contributions' => [
        'don_1' => [
          'financial_type' => 'Donation',
          'amount' => 45
        ],
        'don_2' => [
          'financial_type' => 'Donation',
          'amount' => 12
        ]
      ]
    ];
  }

  public function dataProviderValidWorkflow() {
    // return a sequence of updatestatus to do.
    // At the end of the test, everything should be as in the last state.
    return [
      'init=>pending' => [
        [['status' => 'pending', 'payment_instrument' => 'Debit Card']]
      ],
      'init=>pending with numerical payment instrument' => [
        [['status' => 'pending', 'payment_instrument' => 4]]
      ],
      'init=>completed' => [
        [['status' => 'completed', 'payment_instrument' => 'Debit Card']]
      ],
      'init=>pending=>completed' => [
        [
          ['status' => 'pending', 'payment_instrument' => 'Debit Card'],
          ['status' => 'completed', 'payment_instrument' => 'Debit Card']
        ]
      ],
      'init=>cancelled' => [
        [['status' => 'cancelled', 'payment_instrument' => 'Debit Card']]
      ],
      'init=>failed' => [
        [['status' => 'failed', 'payment_instrument' => 'Debit Card']]
      ],
      'init=>completed=>refunded' => [
        [
          ['status' => 'completed', 'payment_instrument' => 'Debit Card'],
          ['status' => 'refunded', 'payment_instrument' => 'Debit Card']
        ]
      ],
      'init=>refunded' => [
        [['status' => 'refunded', 'payment_instrument' => 'Debit Card']]
      ]
    ];
  }

  public function testInit() {
    $idx = 'test/testInit';
    $params = $this->getSimpleStartParams($idx);
    $result = civicrm_api3('Campagnodon', 'start', $params);

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->addWhere('idx', '=', $idx)
      ->execute()
      ->single();
    $this->assertEquals($transaction['status'], 'init', 'Status is init');

    $contributions = \Civi\Api4\Contribution::get()
      ->addSelect('*', 'contribution_status_id:name', 'payment_instrument_id:name')
      ->addJoin(
        'CampagnodonTransactionLink AS tlink',
        'INNER', null,
        ['tlink.entity_table', '=', '"civicrm_contribution"'],
        ['tlink.entity_id', '=', 'id']
      )
      ->addWhere('tlink.campagnodon_tid', '=', $transaction['id'])
      ->execute()
      ->indexBy('id');
    foreach ($contributions as $cid => $contribution) {
      $this->assertEquals($contribution['contribution_status_id'], 2, 'Contribution '.$cid.' is in status 2');
      $this->assertEquals($contribution['contribution_status_id:name'], 'Pending', 'Contribution '.$cid.' is in status pending');
      // FIXME: CiviCRM sets a default payment instrument. Should have an «unknown» type...
      // $this->assertEquals($contribution['payment_instrument_id'], null, 'Contribution '.$cid.' has no payment_instrument');
    }
  }

  /**
   * @dataProvider dataProviderValidWorkflow
   */
  public function testWorkflow($update_params) {
    $idx = 'test/'.($this->idx_cpt++);
    $params = $this->getSimpleStartParams($idx);
    civicrm_api3('Campagnodon', 'start', $params);

    foreach ($update_params as $step) {
      $result = civicrm_api3('Campagnodon', 'updatestatus', array(
        'transaction_idx' => $idx,
        'status' => $step['status'],
        'payment_instrument' => $step['payment_instrument']
      ));
      $last_step = $step;
    }

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->addWhere('idx', '=', $idx)
      ->execute()
      ->single();

    $this->assertEquals($transaction['status'], $last_step['status']);
    
    $contributions = \Civi\Api4\Contribution::get()
      ->addSelect('*', 'contribution_status_id:name', 'payment_instrument_id:name')
      ->addJoin(
        'CampagnodonTransactionLink AS tlink',
        'INNER', null,
        ['tlink.entity_table', '=', '"civicrm_contribution"'],
        ['tlink.entity_id', '=', 'id']
      )
      ->addWhere('tlink.campagnodon_tid', '=', $transaction['id'])
      ->execute()
      ->indexBy('id');
    foreach ($contributions as $cid => $contribution) {
      $wanted_contribution_status = 'not this';
      $wanted_contribution_status_name = 'not this';
      switch ($last_step['status']) {
        case 'init':
        case 'pending':
          $wanted_contribution_status = 2;
          $wanted_contribution_status_name = 'Pending';
          break;
        case 'completed':
          $wanted_contribution_status = 1;
          $wanted_contribution_status_name = 'Completed';
          break;
        case 'cancelled':
          $wanted_contribution_status = 3;
          $wanted_contribution_status_name = 'Cancelled';
          break;
        case 'failed':
          $wanted_contribution_status = 4;
          $wanted_contribution_status_name = 'Failed';
          break;
        case 'refunded':
          $wanted_contribution_status = 7;
          $wanted_contribution_status_name = 'Refunded';
          break;
      }

      $this->assertEquals($contribution['contribution_status_id'], $wanted_contribution_status, 'Contribution '.$cid.' is in status '.$wanted_contribution_status);
      $this->assertEquals($contribution['contribution_status_id:name'], $wanted_contribution_status_name, 'Contribution '.$cid.' status name is '.$wanted_contribution_status_name);

      if (is_numeric($last_step['payment_instrument'])) {
        $this->assertEquals($contribution['payment_instrument_id'], $last_step['payment_instrument'], 'Contribution '.$cid.' financial_type is '.$last_step['payment_instrument']);
      } else {
        $this->assertEquals($contribution['payment_instrument_id:name'], $last_step['payment_instrument'], 'Contribution '.$cid.' financial_type is '.$last_step['payment_instrument']);
      }
    }
  }

  // TODO: tests on optional_subscriptions (for when=completed)
}
