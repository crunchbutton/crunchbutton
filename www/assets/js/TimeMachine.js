/**
 * Time traveling machine
 *
 * <example>
 * var DeLorean = new TimeMachine();
 * console.log(DeLorean.now());
 * DeLorean.travel('November 5, 1955');
 * console.log(DeLorean.now());
 * </example>
 *
 * It's not called Tardis as it doesn't travel in space.
 *
 * @returns {TimeMachine}
 */
function TimeMachine() {
	'use strict';
	var _fixedTime = null;

	/**
	 * If there is any time traveled stored, load it.
	 *
	 *  @return void
	 */
	this.__construct = function()
	{
		if ($.cookie('TimeMachine')) {
			_fixedTime = $.cookie('TimeMachine');
		}
	}

	/**
	 * Returns the date of when the time machine has landed
	 *
	 * @return Date
	 */
	this.now = function()
	{
		'use strict';
		return (_fixedTime) ? _fixedTime : Date.now();
	}

	/**
	 * Returns the time machine to the present
	 *
	 * @return void
	 */
	this.returnToPresent = function()
	{
		'use strict';
		_fixedTime = false;
	}

	this.toBeContinued = function()
	{
		$.cookie('TimeMachine', _fixedTime);
	}

	/**
	 * Sets the time machine to the specific time
	 *
	 * @return void
	 */
	this.travel = function(newTime)
	{
		'use strict';
		_fixedTime = Date.parse(newTime);
	}

	this.__construct();
}
var DeLorean = new TimeMachine();