<?php
	class MetricsHelperTest extends PHPUnit_Framework_TestCase {
		public function TestRelativeTimeConversion () {
			// force import of all the names in API metrics
			new Crunchbutton_api_metrics();
			$end = date_create_from_format('Y-m-d', '2015-02-01');
			/* $this->assertEqual(Metrics_Helpers::convertTime('now', $end), $end); */
			$this->assertEqual(Metrics_Helpers::convertTime('-1d', $end, Metrics_Helper::START_PERIOD), date_create_from_format('Y-m-d', '2015-01-31'));
			$this->assertEqual(Metrics_Helpers::convertTime('-5h', $end, Metrics_Helper::START_PERIOD), date_create_from_format('Y-m-d H:i', '2015-01-31 19:00'));
			// 0 <period> resets to start of period
			$complexFormat = 'Y-m-d H:i:s';
			$complexEnd = date_create_from_format($complexFormat, '2015-08-05 10:22:36');
			$this->assertEqual(Metrics_Helpers::convertTime('-0d', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2015-08-05 00:00:00'));
			$this->assertEqual(Metrics_Helpers::convertTime('-0d', $complexEnd, Metrics_Helper::END_PERIOD), date_create_from_format($complexFormat, '2015-08-05 23:59:59'));
			$this->assertEqual(Metrics_Helpers::convertTime('-0h', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2015-08-05 10:00:00'));
			$this->assertEqual(Metrics_Helpers::convertTime('-0m', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2015-08-05 10:22:00'));
			$this->assertEqual(Metrics_Helpers::convertTime('-0M', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2015-08-01 00:00:00'));
			$this->assertEqual(Metrics_Helpers::convertTime('-8M', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2014-12-01 00:00:00'));
			// should go to Sunday
			$this->assertEqual(Metrics_Helpers::convertTime('-0W', $complexEnd, Metrics_Helper::START_PERIOD), date_create_from_format($complexFormat, '2015-08-02 00:00:00'));
			// should go to Saturday at end of week
			$this->assertEqual(Metrics_Helpers::convertTime('-0W', $complexEnd, Metrics_Helper::END_PERIOD), date_create_from_format($complexFormat, '2015-08-08 23:59:59'));
			// should handle days in month correctly on weird months
			$this->assertEqual(Metrics_Helpers::convertTime('+1M', date_create('2015-01-05'), Metrics_Helper::END_PERIOD), date_create_from_format($complexFormat, '2015-02-28 23:59:59'));
			// should deal with spanning start and end of years with months
			$this->assertEqual(Metrics_Helpers::convertTime('-3M', date_create('2015-02-05'), Metrics_Helper::END_PERIOD), date_create_from_format($complexFormat, '2014-12-31 23:59:59'));
		}
	}
?>
