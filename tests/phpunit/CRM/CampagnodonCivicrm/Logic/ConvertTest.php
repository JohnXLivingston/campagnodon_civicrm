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
class CRM_CampagnodonCivicrm_Logic_ConvertTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  protected $membership_type_rolling_id = null;
  protected $membership_type_fixed_id = null;

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
  }

  public function tearDown():void {
    parent::tearDown();
  }

  private function _getMinimalConvertParams() {
    return [
      'campagnodon_version' => '1',
      'operation_type' => 'donation',
    ];
  }

  /**
   * 
   */
  public function testNormalizeFinancialTypeId() {
    $this->assertEquals(
      '2',
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeFinancialTypeId('2'),
      'Numeric values remains itself'
    );
    $this->assertEquals(
      '2',
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeFinancialTypeId(2),
      'int becomes strings'
    );
    $this->assertEquals(
      '1',
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeFinancialTypeId('Donation'),
      'Donation becomes 1'
    );
    $this->expectException(API_Exception::class, 'should fail if the financial type does not exists');
    CRM_CampagnodonCivicrm_Logic_Convert::normalizeFinancialTypeId('Missing type');
  }

  public function testNormalizeMembershipTypeId() {
    $this->assertEquals(
      strval($this->membership_type_fixed_id),
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeMembershipTypeId(strval($this->membership_type_fixed_id)),
      'Numeric values remains itself'
    );
    $this->assertEquals(
      strval($this->membership_type_fixed_id),
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeMembershipTypeId(intval($this->membership_type_fixed_id)),
      'int becomes strings'
    );
    $this->assertEquals(
      strval($this->membership_type_fixed_id),
      CRM_CampagnodonCivicrm_Logic_Convert::normalizeMembershipTypeId('Fixed'),
      'Fixed becomes the id'
    );
    $this->expectException(API_Exception::class, 'should fail if the membership type does not exists');
    CRM_CampagnodonCivicrm_Logic_Convert::normalizeMembershipTypeId('Missing type');
  }

  public function dataTestGetConvertFinancialTypeMap() {
    return [
      'missing convert_financial_type' => [
        // params:
        $this->_getMinimalConvertParams(),
        // expects:
        []
      ],
      'empty convert_financial_type' => [
        array_merge($this->_getMinimalConvertParams(), [
          'convert_financial_type' => []
        ]),
        []
      ],
      'keeps financial_type as ids' => [
        array_merge($this->_getMinimalConvertParams(), [
          'convert_financial_type' => [
            '2' => [
              'new_financial_type' => '1',
              'membership' => null
            ]
          ]
        ]),
        [
          '2' => [
            'new_financial_type_id' => '1',
            'membership_id' => null
          ]
        ]
      ],
      'converts financial_type to ids' => [
        array_merge($this->_getMinimalConvertParams(), [
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => null
            ]
          ]
        ]),
        [
          '2' => [
            'new_financial_type_id' => '1',
            'membership_id' => null
          ]
        ]
      ],
      'keeps membership as ids' => [
        array_merge($this->_getMinimalConvertParams(), [
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => $this->membership_type_fixed_id
            ],
            'Donation' => [
              'new_financial_type' => 'Member Dues',
              'membership' => $this->membership_type_rolling_id
            ]
          ]
        ]),
        [
          '2' => [
            'new_financial_type_id' => '1',
            'membership_id' => $this->membership_type_fixed_id
          ],
          '1' => [
            'new_financial_type_id' => '2',
            'membership_id' => $this->membership_type_rolling_id
          ]
        ]
      ],
      'converts membership to ids' => [
        array_merge($this->_getMinimalConvertParams(), [
          'convert_financial_type' => [
            'Member Dues' => [
              'new_financial_type' => 'Donation',
              'membership' => 'Fixed'
            ],
            'Donation' => [
              'new_financial_type' => 'Member Dues',
              'membership' => 'Rolling'
            ]
          ]
        ]),
        [
          '2' => [
            'new_financial_type_id' => '1',
            'membership_id' => $this->membership_type_fixed_id
          ],
          '1' => [
            'new_financial_type_id' => '2',
            'membership_id' => $this->membership_type_rolling_id
          ]
        ]
      ]
    ];
  }

  /**
   * @dataProvider dataTestGetConvertFinancialTypeMap
   */
  public function testGetConvertFinancialTypeMap($params, $expects): void {
    $convert = new CRM_CampagnodonCivicrm_Logic_Convert($params);
    $this->assertEquals($expects, $convert->getConvertFinancialTypeMap());
  }
}
