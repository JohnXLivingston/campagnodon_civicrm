<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Campagnodon.Convert API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Campagnodon_ConvertTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  protected $membership_type_rolling_id = null;
  protected $membership_type_fixed_id = null;

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
    if (!$this->membership_type_rolling_id) {
      $mt = \Civi\Api4\MembershipType::create()->setValues([
        'name' => 'Rolling',
        'duration_unit' => 'year',
        'duration_interval' => 1,
        'period_type' => 'rolling',
        'member_of_contact_id' => 1,
        'domain_id' => 1,
        'financial_type_id' => 2,
        'is_active' => 1,
        'visibility' => 'Public',
      ])->execute()->single();

      $this->membership_type_rolling_id = $mt['id'];
    }
    if (!$this->membership_type_fixed_id) {
      $mt = \Civi\Api4\MembershipType::create()->setValues([
        'name' => 'Fixed',
        'duration_unit' => 'year',
        'duration_interval' => 1,
        'period_type' => 'fixed',
        'member_of_contact_id' => 1,
        'domain_id' => 1,
        'financial_type_id' => 2,
        'is_active' => 1,
        'visibility' => 'Public',
      ])->execute()->single();

      $this->membership_type_fixed_id = $mt['id'];
    }

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

  private $current_idx = 1;
  private function getSimpleMembershipStartParams($keep_optional_subscriptions = null, $optional_subscriptions = null) {
    $idx = 'test/'.$this->current_idx++;
    $r = [
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => $idx,
      'operation_type' => 'membership',
      'contributions' => [
        'membership_1' => [
          'financial_type' => 'Member Dues',
          'amount' => 45,
          'currency' => 'EUR',
          'membership' => $this->membership_type_rolling_id
        ],
        'membership_2' => [
          'financial_type' => 'Member Dues',
          'amount' => 12,
          'currency' => 'EUR',
          'membership' => $this->membership_type_fixed_id
        ]
        // TODO: add an optionnal donation contribution.
      ]
    ];

    if ($keep_optional_subscriptions) {
      $r['keep_optional_subscriptions'] = $keep_optional_subscriptions;
    }
    if ($optional_subscriptions) {
      $r['optional_subscriptions'] = $keep_optional_subscriptions;
    }

    return $r;
  }

  /**
   * Provide valid data for unit tests.
   */
  public function dataTestProviders() {
    return [
      'simple use case' => [[
        'start' => $this->getSimpleMembershipStartParams(),
        'convert' => [
          'campagnodon_version' => '1',
          'operation_type' => 'donation',
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => null // remove the membership
            ]
          ]
        ]
      ]],
      'simple use case, using ids for convert_financial_type' => [[
        'start' => $this->getSimpleMembershipStartParams(),
        'convert' => [
          'campagnodon_version' => '1',
          'operation_type' => 'donation',
          'convert_financial_type' => [
            '2' => [
              'new_financial_type' => '1',
              'membership' => null // remove the membership
            ]
          ]
        ]
      ]],
      'simple use case, in pending status' => [[
        'start' => $this->getSimpleMembershipStartParams(),
        'updatestatus' => [
          'status' => 'pending',
          'payment_instrument' => 'Debit Card'
        ],
        'convert' => [
          'campagnodon_version' => '1',
          'operation_type' => 'donation',
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => null // remove the membership
            ]
          ]
        ]
      ]],
      'simple use case, in completed status. Must fail' => [[
        'start' => $this->getSimpleMembershipStartParams(),
        'updatestatus' => [
          'status' => 'completed',
          'payment_instrument' => 'Debit Card'
        ],
        'convert' => [
          'campagnodon_version' => '1',
          'operation_type' => 'donation',
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => null // remove the membership
            ]
          ]
        ],
        'expect_exception' => CiviCRM_API3_Exception::class
      ]],
      'simple use case, trying to convert to the same operation_type. Must fail' => [[
        'start' => $this->getSimpleMembershipStartParams(),
        'convert' => [
          'campagnodon_version' => '1',
          'operation_type' => 'membership'
        ],
        'expect_exception' => CiviCRM_API3_Exception::class
      ]],
    ];
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
   * Test that we are able to create a transaction.
   */
  public function testInit() {
    $params = $this->getSimpleMembershipStartParams();
    $idx = $params['transaction_idx'];
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
      $this->assertEquals($contribution['financial_type_id'], 2, 'Contribution '.$cid.' has a financial type equal to 2');
    }
  }

  /**
   * Test that the API fails with an invalid campagnodon_version
   */
  public function testWrongAPIVersion() {
    $params = $this->getSimpleMembershipStartParams();
    $idx = $params['transaction_idx'];
    $result = civicrm_api3('Campagnodon', 'start', $params);

    $this->expectException(CiviCRM_API3_Exception::class);
    civicrm_api3('Campagnodon', 'convert', [
      'campagnodon_version' => '0',
      'transaction_idx' => $idx,
      'operation_type' => 'donation',
      'keep_optional_subscriptions' => [],
      'optional_subscriptions' => [],
    ]);
  }

  /**
   * Test that the API fails if the transaction idx does not exist.
   */
  public function testWrongIdx() {
    $idx = 'whatever';
    $this->expectException(CiviCRM_API3_Exception::class);
    civicrm_api3('Campagnodon', 'convert', [
      'campagnodon_version' => '1',
      'transaction_idx' => $idx,
      'operation_type' => 'donation',
      'keep_optional_subscriptions' => [],
      'optional_subscriptions' => [],
    ]);
  }

  /**
   * Test some classic use cases.
   * @dataProvider dataTestProviders
   */
  public function testApiConvert($params) {
    $idx = $params['start']['transaction_idx'];
    $update_status = array_key_exists('updatestatus', $params) ? $params['updatestatus'] : null;
    $expect_exception = array_key_exists('expect_exception', $params) ? $params['expect_exception'] : null;
  
    civicrm_api3('Campagnodon', 'start', $params['start']);
    if ($update_status) {
      civicrm_api3('Campagnodon', 'updatestatus', array_merge(
        array(
          'transaction_idx' => $idx
        ),
        $params['updatestatus']
      ));
    }

    $old_transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('idx', '=', $idx)
      ->execute()
      ->single();

    $this->assertEquals($old_transaction['status'], $update_status ? $update_status['status'] : 'init', 'The status of the transaction is correct.');

    if ($expect_exception) {
      $this->expectException($expect_exception);
    }
    $result = civicrm_api3('Campagnodon', 'convert', array_merge(
      ['transaction_idx' => $idx],
      $params['convert']
    ));
    if ($expect_exception) {
      return;
    }

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $old_transaction['id'])
      ->execute()
      ->single();
    
    
    $this->assertNotEquals($old_transaction['operation_type'], $transaction['operation_type'], 'The operation type has changed.');
    $this->assertEquals($transaction['operation_type'], $params['convert']['operation_type'], 'The operation type is the correct one.');


    // TODO: test that contribution financial_type have changed
    // TODO: test that transctionlink financial_type have changed
  }
}
