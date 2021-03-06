<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Config;
use Piwik\Date;
use Piwik\Tests\Fixtures\ManyVisitsWithMockLocationProvider;
use Piwik\Tests\IntegrationTestCase;

/**
 * @group Integration
 * @group PivotByQueryParamTest
 */
class PivotByQueryParamTest extends IntegrationTestCase
{
    /**
     * @var ManyVisitsWithMockLocationProvider
     */
    public static $fixture = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        Config::getInstance()->General['pivot_by_filter_enable_fetch_by_segment'] = 1;
    }

    public function test_PivotBySubtableDimension_CreatesCorrectPivotTable()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1,
            'disable_queued_filters' => 1 // test that prepending doesn't happen w/ this
        ));
    }

    public function test_PivotBySubtableDimension_CreatesCorrectPivotTable_WhenEntireHirearchyIsNotLoaded()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => '', // also test default pivot column
            'pivotByColumnLimit' => -1,
            'expanded' => 0
        ));
    }

    public function test_PivotBySegment_CreatesCorrectPivotTable()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotBySegment_CreatesCorrectPivotTable_WhenSegmentUsedInRequest()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => 'browserCode==FF',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotByParam_PlaysNiceWithOtherQueryParams()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1,
            'flat' => 1,
            'totals' => 1,
            'disable_queued_filters' => 1,
            'disable_generic_filters' => 1,
            'showColumns' => 'Google,Bing'
        ));
    }

    public function test_PivotByParam_PlaysNiceWithQueuedFilters()
    {
        // TODO: known issue: some segment/report relationships are more complicated; for example, UserCountry.GetCity labels are combinations
        // of city, region & country dimensions, so the segment to get an intersected table needs all 3 of those.

        $this->markTestSkipped("Not working right now.");

        $this->assertApiResponseEqualsExpected("UserSettings.getBrowser", array( // should have logo metadata in output
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City', // testing w/ report that has no subtable report
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotByParam_WorksWithReportWhoseSubtableIsSelf()
    {
        $this->assertApiResponseEqualsExpected("Actions.getPageUrls", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Actions.PageUrl',
            'pivotByColumn' => 'nb_hits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotByParam_WorksWithColumnLimiting()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => 2
        ));
    }

    public function test_PivotByParam_WorksWithJsonOutput()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'format' => 'json',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotByParam_WorksWithCsvOutput()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'format' => 'csv',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function test_PivotByParam_PlaysNiceWithDataTableMaps()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => 'all',
            'date' => '2010-01-01,2010-01-07',
            'period' => 'day',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }
}

PivotByQueryParamTest::$fixture = new ManyVisitsWithMockLocationProvider();