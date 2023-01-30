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

  private function getSimpleStartParams($idx) {
    return [
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => $idx,
      'operation_type' => 'donation',
      'contributions' => [
        'don_1' => [
          'financial_type' => 'Donation',
          '_financial_type_id' => 1, // this is only there for unit tests.
          'amount' => 45,
          'currency' => 'EUR'
        ],
        'don_2' => [
          'financial_type' => 'Donation',
          '_financial_type_id' => 1, // this is only there for unit tests.
          'amount' => 12,
          'currency' => 'EUR'
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
      'init=>completed=>pending' => [
        [
          ['status' => 'completed', 'payment_instrument' => 'Debit Card'],
          ['status' => 'pending', 'payment_instrument' => 'Debit Card']
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
      ],
      'init=>completed=>double_membership' => [
        [
          ['status' => 'completed', 'payment_instrument' => 'Debit Card'],
          ['status' => 'double_membership', 'payment_instrument' => 'Debit Card']
        ]
      ],
      'init=>double_membership' => [
        [['status' => 'double_membership', 'payment_instrument' => 'Debit Card']]
      ]
    ];
  }

  public function testInit() {
    $idx = 'test/testInit';
    $params = $this->getSimpleStartParams($idx);
    $result = civicrm_api3('Campagnodon', 'start', $params);

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('idx', '=', $idx)
      ->execute()
      ->single();
    $this->assertEquals($transaction['status'], 'init', 'Status is init');

    $contributions = \Civi\Api4\Contribution::get()
      ->setCheckPermissions(false)
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
      $this->assertEquals($contribution['receive_date'], $transaction['contribution_date'], 'Contribution receive date equals transaction contribution_date');
      // FIXME: CiviCRM sets a default payment instrument. Should have an «unknown» type...
      // $this->assertEquals($contribution['payment_instrument_id'], null, 'Contribution '.$cid.' has no payment_instrument');
    }
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
   * @dataProvider dataProviderValidWorkflow
   */
  public function testWorkflow($update_params) {
    $idx = 'test/'.($this->idx_cpt++);
    $params = $this->getSimpleStartParams($idx);
    civicrm_api3('Campagnodon', 'start', $params);

    $cpt = 1;
    foreach ($update_params as $step) {
      $result = civicrm_api3('Campagnodon', 'updatestatus', array(
        'transaction_idx' => $idx,
        'status' => $step['status'],
        'payment_instrument' => $step['payment_instrument']
      ));
      $result_line = array_pop($result['values']);
      $result_status = $result_line['status'];
      $this->assertEquals($step['status'], $result_status, 'The status at the step '.($cpt++).' is correct');
      $last_step = $step;
    }

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('idx', '=', $idx)
      ->execute()
      ->single();

    $this->assertEquals($transaction['status'], $last_step['status'], 'The final status of the transaction is correct.');
    
    $contributions = \Civi\Api4\Contribution::get()
      ->setCheckPermissions(false)
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

    $wanted_contribution_status = 'not this';
    $wanted_contribution_status_name = 'not this';
    $count_must_be_0 = false;
    switch ($last_step['status']) {
      case 'init':
      case 'pending':
        $wanted_contribution_status = 2;
        $wanted_contribution_status_name = 'Pending';
        // If there is at least one step with status !=init/pending, there should be contributions
        // <=> must be 0 contribution if there are 0 non_pending_status
        $non_pending_status = array_filter($update_params, function ($step) {
          return $step['status'] !== 'init' && $step['status'] !== 'pending';
        });
        $count_must_be_0 = count($non_pending_status) === 0;
        break;
      case 'completed':
        $wanted_contribution_status = 1;
        $wanted_contribution_status_name = 'Completed';
        break;
      case 'double_membership':
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

    if ($count_must_be_0) {
      $this->assertTrue($contributions->count() === 0, 'There should not be any contribution for now');
    } else {
      $this->assertTrue($contributions->count() > 0, 'Contributions are created');
      $this->assertEquals($contributions->count(), count($params['contributions']), 'There are the correct number for contributions created');
    }
    foreach ($contributions as $cid => $contribution) {
      $this->assertEquals($contribution['contribution_status_id'], $wanted_contribution_status, 'Contribution '.$cid.' is in status '.$wanted_contribution_status);
      $this->assertEquals($contribution['contribution_status_id:name'], $wanted_contribution_status_name, 'Contribution '.$cid.' status name is '.$wanted_contribution_status_name);

      if (is_numeric($last_step['payment_instrument'])) {
        $this->assertEquals($contribution['payment_instrument_id'], $last_step['payment_instrument'], 'Contribution '.$cid.' financial_type is '.$last_step['payment_instrument']);
      } else {
        $this->assertEquals($contribution['payment_instrument_id:name'], $last_step['payment_instrument'], 'Contribution '.$cid.' financial_type is '.$last_step['payment_instrument']);
      }

      $this->assertEquals($contribution['receive_date'], $transaction['contribution_date'], 'Contribution receive date equals transaction contribution_date');

      // Now testing that the contribution is correct!
      // Getting the first match in the params array
      $first_match = current(array_filter($params['contributions'], function ($v) use ($contribution) {
        return $v['amount'] == $contribution['total_amount']
          && $v['_financial_type_id'] === $contribution['financial_type_id']
          && $v['currency'] === $contribution['currency'];
      }));

      $this->assertTrue(!empty($first_match), 'Contribution '.$cid.' found in the start params.');
    }
  }

  // TODO: tests on group optional_subscriptions (for when=completed)

  // TODO: tests on opt_in optional_subscriptions (when=completed)

  // TODO: add tests on recurring_status field
}
