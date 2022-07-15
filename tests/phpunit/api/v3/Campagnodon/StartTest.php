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
  protected $newsletter_group = null;

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
    if (!$this->newsletter_group) {
      $this->newletter_group = \Civi\Api4\Group::create()
        ->setCheckPermissions(false)
        ->addValue('name', 'Newsletter Subscribers')
        ->addValue('title', 'The Newsletter group')
        ->execute()->single();
    }
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

  /**
   * Provide valid contacts for unit tests.
   */
  public function dataTestProviders() {
    // TODO: add some tests with a campaign_id
    return [
      'john' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'payment_url' => 'https://www.example.com?test=1&test=2', // adding some & to test that there is no url encoding.
        'country' => 'FR',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            '_financial_type_id' => 1, // this is only there for unit tests.
            'amount' => 12,
            'currency' => 'EUR'
          ]
        ],
        'optional_subscriptions' => [
          ['type' => 'opt-in', 'key' => 'do_not_trade', 'when' => 'init']
        ]
      )],
      'bill' => [array(
        'campagnodon_version' => '1',
        'email' => 'bill.smith@example.com',
        'transaction_idx' => 'test/123456789123456789',
        'tax_receipt' => true,
        'prefix' => 2,
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'birth_date' => '1981-01-01',
        'phone' => '+33(0) 123456789',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45,
            'currency' => 'EUR'
          ]
        ]
      )],
      'billy' => [array(
        'campagnodon_version' => '1',
        'email' => 'billy.smith@example.com',
        'transaction_idx' => 'test/123456789123456789',
        'tax_receipt' => false,
        'prefix' => 'Mr.',
        'first_name' => 'Billy',
        'last_name' => 'Smith',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45,
            'currency' => 'EUR'
          ]
        ]
      )],
      'test with 2 donations' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 12,
            'currency' => 'EUR'
          ],
          'don2' => [
            'financial_type' => 'Donation',
            'amount' => 24,
            'currency' => 'EUR'
          ]
        ]
      )],
      'test with optional_subscriptions on init' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'country' => 'FR',
        'optional_subscriptions' => [
          ['type' => 'group', 'key' => 'Newsletter Subscribers', 'when' => 'init']
        ],
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            '_financial_type_id' => 1, // this is only there for unit tests.
            'amount' => 12,
            'currency' => 'EUR'
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
      'must fail because of missing campagnodon_version' => [array(
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'country' => 'FR',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            '_financial_type_id' => 1, // this is only there for unit tests.
            'amount' => 12,
            'currency' => 'EUR'
          ]
        ],
        'optional_subscriptions' => [
          ['type' => 'opt-in', 'key' => 'do_not_trade', 'when' => 'init']
        ]
      )],
      'must fail because of invalid campagnodon_version' => [array(
        'campagnodon_version' => '2',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/1',
        'country' => 'FR',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            '_financial_type_id' => 1, // this is only there for unit tests.
            'amount' => 12,
            'currency' => 'EUR'
          ]
        ],
        'optional_subscriptions' => [
          ['type' => 'opt-in', 'key' => 'do_not_trade', 'when' => 'init']
        ]
      )],
      'must fail because of invalid contribution amount' => [array(
        'campagnodon_version' => '1',
        'email' => 'michel.martin@example.com',
        'transaction_idx' => 'test/2',
        'first_name' => 'Michel',
        'last_name' => 'Martin',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 'nonono',
            'currency' => 'EUR'
          ]
        ]
      )],
      // FIXME: seems that CiviCRM does not check values for this type of pseudoConstant.
      // 'must fail because of invalid contribution currency' => [array(
      //   'campagnodon_version' => '1',
      //   'email' => 'michel.martin@example.com',
      //   'transaction_idx' => 'test/2',
      //   'first_name' => 'Michel',
      //   'last_name' => 'Martin',
      //   'contributions' => [
      //     'don' => [
      //       'financial_type' => 'Donation',
      //       'amount' => 12,
      //       'currency' => 'XXX'
      //     ]
      //   ]
      // )],
      'must fail because of unknown campaign' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'campaign_id' => '123456749',
        'transaction_idx' => 'test/3',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 12,
            'currency' => 'EUR'
          ]
        ]
      )],
      'must fail because of invalid payment_url' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/4',
        'payment_url' => 'this is not url /',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 12,
            'currency' => 'EUR'
          ]
        ]
      )],
      // FIXME: seems that CiviCRM does not check values for this type of pseudoConstant.
      // 'must fail because of invalid country code' => [array(
      //   'campagnodon_version' => '1',
      //   'email' => 'john.doe@example.com',
      //   'transaction_idx' => 'test/5',
      //   'country' => 'invalid country code',
      //   'contributions' => [
      //     'don' => [
      //       'financial_type' => 'Donation',
      //       '_financial_type_id' => 1, // this is only there for unit tests.
      //       'amount' => 12,
      //       'currency' => 'EUR'
      //     ]
      //   ]
      // )],
      'must fail because invalid prefix (numerical)' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/6',
        'prefix' => 123456,
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45,
            'currency' => 'EUR'
          ]
        ]
      )],
      'must fail because invalid prefix' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/7',
        'prefix' => 'no way',
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45,
            'currency' => 'EUR'
          ]
        ]
      )],
      'must fail because invalid birth_date' => [array(
        'campagnodon_version' => '1',
        'email' => 'john.doe@example.com',
        'transaction_idx' => 'test/8',
        'first_name' => 'Bill',
        'last_name' => 'Smith',
        'birth_date' => 'not a date',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => 45,
            'currency' => 'EUR'
          ]
        ]
      )],
      // FIXME: seems that CiviCRM does not check values for boolean
      // 'must fail because invalid tax_receipt' => [array(
      //   'campagnodon_version' => '1',
      //   'email' => 'john.doe@example.com',
      //   'transaction_idx' => 'test/8',
      //   'tax_receipt' => 'not a boolean',
      //   'first_name' => 'Bill',
      //   'last_name' => 'Smith',
      //   'contributions' => [
      //     'don' => [
      //       'financial_type' => 'Donation',
      //       'amount' => 45,
      //       'currency' => 'EUR'
      //     ]
      //   ]
      // )],
      'must fail because of an invalid opt-in' => [array(
        'campagnodon_version' => '1',
        'email' => 'michel.martin@example.com',
        'transaction_idx' => 'test/1025201',
        'first_name' => 'Michel',
        'last_name' => 'Martin',
        'contributions' => [
          'don' => [
            'financial_type' => 'Donation',
            'amount' => '12',
            'currency' => 'EUR'
          ]
        ],
        'optional_subscriptions' => [
          ['type' => 'opt-in', 'key' => 'does_not_exist', 'when' => 'init']
        ]
      )],
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
   * Test campagnodon start API. Must fail if no contribution.
   */
  public function testApiStartWithoutContribution() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
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
      'campagnodon_version' => '1',
      'transaction_idx' => 'test/20',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45,
          'currency' => 'EUR'
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
      'campagnodon_version' => '1',
      'email' => 'bill.smith@example.com',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 45,
          'currency' => 'EUR'
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

    // Trying to get the transaction by ID
    $obj = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addSelect('*', 'country_id:name', 'prefix_id:name')
      ->addWhere('id', '=', $transaction['id'])
      ->execute()
      ->single();
    $this->assertTrue(!empty($obj), 'Can get Transaction by id');
    $this->assertEquals($obj['id'], $transaction['id'], 'Can get the good Transaction by id');
    $this->assertEquals($obj['status'], 'init', 'The transaction status is init');
    $this->assertEquals($obj['payment_instrument_id'], null, 'payment_instrument_id is null');
    $this->assertEquals($obj['payment_url'], empty($params['payment_url']) ? null : $params['payment_url'], 'payment url should be correct');
    $this->assertEquals($obj['country_id:name'], empty($params['country']) ? null : $params['country'], 'country code must be correct');
    $this->assertEquals($obj['first_name'], empty($params['first_name']) ? null : $params['first_name'], 'first_name must be correct');
    $this->assertEquals($obj['last_name'], empty($params['last_name']) ? null : $params['last_name'], 'last_name must be correct');
    $this->assertEquals($obj['phone'], empty($params['phone']) ? null : $params['phone'], 'phone must be correct');
    $this->assertEquals($obj['birth_date'], empty($params['birth_date']) ? null : $params['birth_date'], 'birth_date must be correct');

    $this->assertEquals($obj['tax_receipt'], !empty($params['tax_receipt']) && $params['tax_receipt'] ? true : false, 'tax_receipt must be correct');
    if (empty($params['prefix'])) {
      $this->assertEquals($obj['prefix_id'], null, 'prefix_id must be null');
    } else if (is_numeric($params['prefix'])) {
      $this->assertEquals($obj['prefix_id'], $params['prefix'], 'prefix_id must be correct');
    } else {
      $this->assertEquals($obj['prefix_id:name'], $params['prefix'], 'prefix_id:name must be correct');
    }

    if (!empty($params['transaction_idx'])) {
      $obj = \Civi\Api4\CampagnodonTransaction::get()
        ->setCheckPermissions(false)
        ->addWhere('idx', '=', $params['transaction_idx'])
        ->execute()
        ->single();

        $this->assertTrue(!empty($obj), 'Can get Transaction by idx');
        $this->assertEquals($obj['id'], $transaction['id'], 'Can get the good Transaction by idx');
    }

    // Getting the contact. Will be used later on.
    $contact = \Civi\Api4\Contact::get()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $transaction['contact_id'])
      ->execute()->single();


    // Testing that transaction_links and contributions are created
    $contribs = $params['contributions'] ?? [];
    $contributions = \Civi\Api4\Contribution::get()
      ->setCheckPermissions(false)
      ->addSelect('*', 'financial_type_id:name')
      ->addJoin(
        'CampagnodonTransactionLink AS tlink',
        'INNER', null,
        ['tlink.entity_table', '=', '"civicrm_contribution"'],
        ['tlink.entity_id', '=', 'id']
      )
      ->addWhere('tlink.campagnodon_tid', '=', $transaction['id'])
      ->execute();
    $this->assertEquals(count($contributions), 0, 'Contribution not yet created');

    $contrib_links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
      ->addSelect('*', 'financial_type_id:name')
      ->addWhere('campagnodon_tid', '=', $transaction['id'])
      ->addWhere('entity_table', '=', '"civicrm_contribution"') // FIXME: should double quotes be there??
      ->execute();
    $this->assertEquals($contrib_links->count(), $contributions->count(), 'Same number of contribution links as number of given contributions');
    $contrib_links->indexBy('id');
    $contrib_links = (array) $contrib_links;
    foreach ($contrib_links as $k => $c) {
      $first_match = current(array_filter($contrib_links, function ($v) use ($c) {
        return $v['total_amount'] == $c['amount'] && $v['financial_type_id:name'] === $c['financial_type'] && $v['currency'] === $c['currency'];
      }));

      $this->assertTrue(!empty($first_match), 'Contribution link number '.$k.' found.');
      if (array_key_exists('_financial_type_id', $c)) {
        $this->assertEquals($first_match['financial_type_id'], $c['_financial_type_id'], 'financial_type_id is ok for contribution link number '.$k);
      }

      if (!empty($first_match)) {
        unset($contributions[$first_match['id']]);
      }
    }
    $this->assertEquals(count($contrib_links), 0, 'No extra contribution link created.');

    // Testing optional_subscriptions
    $optional_subscriptions = $params['optional_subscriptions'] ?? array();
    $optional_subscriptions_group = array_filter($optional_subscriptions, function ($os) {
      return $os['type'] === 'group';
    });
    $optional_subscriptions_group_links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction['id'])
      ->addWhere('entity_table', '=', 'civicrm_group')
      ->execute();
    $this->assertEquals(count($optional_subscriptions_group), $optional_subscriptions_group_links->count(), 'Same number of linked group as number of given optional_subscriptions');
    $optional_subscriptions_group_links->indexBy('id');
    $optional_subscriptions_group_links = (array) $optional_subscriptions_group_links;

    // TODO: test that the GroupContact was created (unless when=completed)

    $optional_subscriptions_opt_in = array_filter($optional_subscriptions, function ($os) {
      return $os['type'] === 'opt-in';
    });
    $optional_subscriptions_contact_links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction['id'])
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->execute();
    $this->assertEquals(count($optional_subscriptions_opt_in), $optional_subscriptions_contact_links->count(), 'Same number of linked group as number of given optional_subscriptions');
    $optional_subscriptions_contact_links->indexBy('id');
    $optional_subscriptions_contact_links = (array) $optional_subscriptions_contact_links;
    foreach ($optional_subscriptions_contact_links as $osid => $os) {
      $this->assertEquals($os['entity_id'], $transaction['contact_id'], 'The opt_in link '.$osid.' has the correct contact_id');
      if (!$os['on_complete']) {
        $this->assertEquals($contact[$os['opt_in']], false, 'Opt-in '.$os['opt_in'].' should be false because the user accepted.');
      } else {
        $this->assertEquals($contact[$os['opt_in']], true, 'Opt-in '.$os['opt_in'].' should be true because the user doesnt accept yet.');
      }
    }
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
    // TODO: add more tests.
    Civi::settings()->set('campagnodon_dedupe_rule', 'Unsupervised/first');
    Civi::settings()->set('campagnodon_dedupe_rule_with_tax_receipt', 'Unsupervised/first');

    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/30',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 12,
          'currency' => 'EUR'
        ]
      ]
    ));

    $this->assertEquals(1, $result['count'], 'Must have 1 result');
    $transaction1 = array_pop($result['values']);

    $contact_id = $transaction1['contact_id'];
    $this->assertTrue(intval($contact_id) > 0, 'Must have a contact_id');

    // Another donation, with a different contact.
    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'bill.smith@example.com',
      'transaction_idx' => 'test/31',
      'first_name' => 'Bill',
      'last_name' => 'Smith',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 34,
          'currency' => 'EUR'
        ]
      ]
    ));
    $this->assertEquals(1, $result['count'], 'Second start must have 1 result');
    $transaction2 = array_pop($result['values']);
    $this->assertTrue($contact_id != $transaction2['contact_id'], 'Second start must have created a new contact');

    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/32',
      'first_name' => 'John',
      'last_name' => 'Doe',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 15,
          'currency' => 'EUR'
        ]
      ]
    ));
    $this->assertEquals(1, $result['count'], 'Third start must have 1 result');
    $transaction3 = array_pop($result['values']);
    $this->assertSame($contact_id, $transaction3['contact_id'], 'Third start must have reused the first contact');
  }

  public function testApiStartUniqueTransactionIdx() {
    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'john.doe@example.com',
      'transaction_idx' => 'test/50',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 12,
          'currency' => 'EUR'
        ]
      ]
    ));

    $this->assertEquals(1, $result['count'], 'Must have 1 result');

    $this->expectException(CiviCRM_API3_Exception::class);
    // Another donation, with same transaction_idx.
    $result = civicrm_api3('Campagnodon', 'start', array(
      'campagnodon_version' => '1',
      'email' => 'bill.smith@example.com',
      'transaction_idx' => 'test/50',
      'contributions' => [
        'don' => [
          'financial_type' => 'Donation',
          'amount' => 34,
          'currency' => 'EUR'
        ]
      ]
    ));
  }

  // TODO: more tests on optional_subscriptions.
  // TODO: test deduplication
}
