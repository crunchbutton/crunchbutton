<?php
	/**
	 * @group Metrics
	 **/
	class MetricsHelperTest extends PHPUnit_Framework_TestCase {
		public function testRelativeTimeConversion () {
			// force import of all the names in API metrics
			new Controller_api_metrics();
			$end = date_create_from_format('Y-m-d', '2015-02-01');
			/* $this->assertEqual(Metrics_Helpers::convertTime('now', $end), $end); */
			$this->assertEqual(Cockpit_Metrics::getStartDate('-1d', $end), date_create_from_format('Y-m-d', '2015-01-31'));
			$this->assertEqual(Cockpit_Metrics::getStartDate('-5h', $end), date_create_from_format('Y-m-d H:i', '2015-01-31 19:00'));
			// 0 <period> resets to start of period
			$complexFormat = 'Y-m-d H:i:s';
			$complexEnd = date_create_from_format($complexFormat, '2015-08-05 10:22:36');
			$this->assertEqual(Cockpit_Metrics::getStartDate('-0d', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 00:00:00'));
			$this->assertEqual(Cockpit_Metrics::getEndDate('-0d', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 23:59:59'));
			$this->assertEqual(Cockpit_Metrics::getStartDate('-0h', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:00:00'));
			$this->assertEqual(Cockpit_Metrics::getStartDate('-0m', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:22:00'));
			$this->assertEqual(Cockpit_Metrics::getStartDate('-0M', $complexEnd), date_create_from_format($complexFormat, '2015-08-01 00:00:00'));
			$this->assertEqual(Cockpit_Metrics::getStartDate('-8M', $complexEnd), date_create_from_format($complexFormat, '2014-12-01 00:00:00'));
			// should go to Sunday
			$this->assertEqual(Cockpit_Metrics::getStartDate('-0W', $complexEnd), date_create_from_format($complexFormat, '2015-08-02 00:00:00'));
            $this->assertEqual(Cockpit_Metrics::getStartDate('-2W', $complexEnd), date_create_from_format($complexFormat, '2015-07-19 00:00:00'));
			// should go to Saturday at end of week
			$this->assertEqual(Cockpit_Metrics::getEndDate('-0W', $complexEnd), date_create_from_format($complexFormat, '2015-08-08 23:59:59'));
            $this->assertEqual(Cockpit_Metrics::getEndDate('-3W', $complexEnd), date_create_from_format($complexFormat, '2015-07-17 23:59:59'));
			// should handle days in month correctly on weird months
			$this->assertEqual(Cockpit_Metrics::getEndDate('+1M', date_create('2015-01-05')), date_create_from_format($complexFormat, '2015-02-28 23:59:59'));
			// should deal with spanning start and end of years with months
			$this->assertEqual(Cockpit_Metrics::getEndDate('-3M', date_create('2015-02-05')), date_create_from_format($complexFormat, '2014-12-31 23:59:59'));
		}
	}
?>
