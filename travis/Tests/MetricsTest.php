<?php
	class MetricsHelperTest extends PHPUnit_Framework_TestCase {
		public function TestRelativeTimeConversion () {
			// force import of all the names in API metrics
			new Crunchbutton_api_metrics();
			$end = date_create_from_format('Y-m-d', '2015-02-01');
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('now', $end), $end);
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-1d', $end), date_create_from_format('Y-m-d', '2015-01-31'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-5h', $end), date_create_from_format('Y-m-d H:i', '2015-01-31 19:00'));
			// 0 <period> resets to start of period
			$complexFormat = 'Y-m-d H:i:s'
			$complexEnd = date_create_from_format($complexFormat, '2015-08-05 10:22:36');
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-0d', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 00:00:00'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-0h', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:00:00'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-0m', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:22:00'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-0M', $complexEnd), date_create_from_format($complexFormat, '2015-08-01 00:00:00'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-8M', $complexEnd), date_create_from_format($complexFormat, '2014-12-01 00:00:00'));
			$this->assertEqual(Metrics_Helpers::convertRelativeTime('-0W', $complexEnd), date_create_from_format($complexFormat, '2015-08-02 00:00:00'));
		}
	}
?>
