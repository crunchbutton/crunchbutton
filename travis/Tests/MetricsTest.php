<?php
	/**
	 * @group Metrics
	 **/
	class MetricsHelperTest extends PHPUnit_Framework_TestCase {
		public function testRelativeTimeConversion () {
			$end = date_create_from_format('Y-m-d', '2015-02-01');
			/* $this->assertEquals(Metrics_Helpers::convertTime('now', $end), $end); */
			$this->assertEquals(Cockpit_Metrics::getStartDate('-1d', $end), date_create_from_format('Y-m-d', '2015-01-31'));
			$this->assertEquals(Cockpit_Metrics::getStartDate('-5h', $end), date_create_from_format('Y-m-d H:i', '2015-01-31 19:00'));
			// 0 <period> resets to start of period
			$complexFormat = 'Y-m-d H:i:s';
			$complexEnd = date_create_from_format($complexFormat, '2015-08-05 10:22:36');
			$this->assertEquals(Cockpit_Metrics::getStartDate('-0d', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 00:00:00'));
			$this->assertEquals(Cockpit_Metrics::getEndDate('-0d', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 23:59:59'));
			$this->assertEquals(Cockpit_Metrics::getStartDate('-0h', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:00:00'));
			$this->assertEquals(Cockpit_Metrics::getStartDate('-0m', $complexEnd), date_create_from_format($complexFormat, '2015-08-05 10:22:00'));
			$this->assertEquals(Cockpit_Metrics::getStartDate('-0M', $complexEnd), date_create_from_format($complexFormat, '2015-08-01 00:00:00'));
			$this->assertEquals(Cockpit_Metrics::getStartDate('-8M', $complexEnd), date_create_from_format($complexFormat, '2014-12-01 00:00:00'));
			// should go to Sunday
			$this->assertEquals(Cockpit_Metrics::getStartDate('-0W', $complexEnd), date_create_from_format($complexFormat, '2015-08-02 00:00:00'));
            $this->assertEquals(Cockpit_Metrics::getStartDate('-2W', $complexEnd), date_create_from_format($complexFormat, '2015-07-19 00:00:00'));
			// should go to Saturday at end of week
			$this->assertEquals(Cockpit_Metrics::getEndDate('-0W', $complexEnd), date_create_from_format($complexFormat, '2015-08-08 23:59:59'));
            $this->assertEquals(Cockpit_Metrics::getEndDate('-3W', $complexEnd), date_create_from_format($complexFormat, '2015-07-17 23:59:59'));
			// should handle days in month correctly on weird months
			$this->assertEquals(Cockpit_Metrics::getEndDate('+1M', date_create('2015-01-05')), date_create_from_format($complexFormat, '2015-02-28 23:59:59'));
			// should deal with spanning start and end of years with months
			$this->assertEquals(Cockpit_Metrics::getEndDate('-3M', date_create('2015-02-05')), date_create_from_format($complexFormat, '2014-12-31 23:59:59'));
		}
		public function testGroupByIndex() {
			// should work for numbers with no dups
			$data = [
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9],
				[10, 1, 2]
			];
			$this->assertEquals(Cockpit_Metrics::groupByIndex($data, 0), [1 => [[1, 2, 3]], 4 => [[4, 5, 6]], 7 => [[7, 8, 9]], 10 => [[10, 1, 2]]]);
			// random doc example
			$ret = Cockpit_Metrics::groupByIndex([[2, 'a', 3], [4, 'c', 5], [1, 'a', 16], []], 1);
			$this->assertEquals($ret, ['a' => [[2, 'a', 3], [1, 'a', 16]], 'c' => [[4, 'c', 5]], null => [[]]]);
		}
	}
?>
