<?php

class Crunchbutton_Session extends Crunchbutton_Session_Adapter implements SessionHandlerInterface {
	public function __construct($id = null) {
		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'gc']
		);
		parent::__construct();
	}

	/**
	 * Setter and getter for a flash message.
	 *
	 * A flash message is a message to be shown only once. If the page is
	 * reloaded, the message should not be shown again. If no param was sent to
	 * be stored, then the stored message will be returned.
	 *
	 * @param string $message If set, that will be the message to be stored.
	 *
	 * @return string
	 */
	static function flashMessage($message = null)
	{
		$key = 'Crunchbutton.Session.flashMessage';
		if ($message) {
			$_SESSION[$key] = $message;
		} else {
			$message = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
			unset($_SESSION[$key]);
		}
		return $message;
	}

	/**
	 * Returns HTML to show a widget with the flashMessage
	 *
	 * The widget follows the style applied in the JQuery UI theme. No widget
	 * is returned if no message was found
	 *
	 * @return string
	 */
	static function flashWidget()
	{
		$html    = '';
		$message = self::flashMessage();
		if ($message) {
			$html = <<<HTML
<div class="ui-state-highlight ui-corner-all" style="padding: 0.7em; margin-bottom: 20px;">
	<p>
		<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		$message
	</p>
</div>
HTML;
		}
		return $html;
	}
}