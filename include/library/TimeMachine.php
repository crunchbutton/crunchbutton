<?php
/**
 * Time traveling machine
 *
 * <example>
 *     $DeLorean = new TimeMachine();
 *     var_log($DeLorean.now());
 *     $DeLorean->travel('November 5, 1955');
 *     var_log(DeLorean->now());
 * </example>
 *
 * It's not called Tardis as it doesn't travel in space.
 */
class TimeMachine
{
	private $_fixedTime = null;
	private $timezone   = null;

	/**
	 * If there is any time traveled stored, load it.
	 *
	 * @param string $timezone The timezone to use.
	 *
	 * @return void
	 *
	 * @link http://www.php.net/manual/en/timezones.php
	 */
	public function __construct($timezone)
	{
		if ($_SESSION['TimeMachine']) {
			$this->_fixedTime = $_SESSION['TimeMachine'];
		}
		$this->_timezone = new DateTimeZone($timezone);
	}

	/**
	 * Returns the date of when the time machine has landed
	 *
	 * @return Date
	 */
	public function now()
	{
		return ($this->_fixedTime) ? $this->_fixedTime : new DateTime();
	}

	/**
	 * Returns the time machine to the present
	 *
	 * @return void
	 */
	function backToThePresent()
	{
		$this->_fixedTime = null;
	}

	function toBeContinued()
	{
		$_SESSION['TimeMachine'] = $this->_fixedTime;
	}

	/**
	 * Sets the time machine to the specific time
	 *
	 * @param string $newTime When to travel to
	 *
	 * @return void
	 */
	function travel($newTime)
	{
		$this->_fixedTime = new DateTime($newTime, $this->_timezone);
	}
}