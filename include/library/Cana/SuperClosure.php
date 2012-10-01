<?php
/**
 * Thanks to Jeremy Lindblom for most of this
 * https://github.com/jeremeamia/super_closure
 */

class Cana_SuperClosure {

	protected $closure = NULL;
	protected $reflection = NULL;
	protected $code = NULL;
	protected $used_variables = array();

	public function __construct($function) {
		if (!$function instanceOf Closure) {
			throw new InvalidArgumentException();
		}

		$this->closure = $function;
		$this->reflection = new ReflectionFunction($function);
		$this->code = $this->_fetchCode();
		$this->used_variables = $this->_fetchUsedVariables();
	}

	public function __invoke() {
		$args = func_get_args();
		return $this->reflection->invokeArgs($args);
	}

	public function getClosure() {
		return $this->closure;
	}

	public function _fetchCode() {
		// Open file and seek to the first line of the closure
		$file = new SplFileObject($this->reflection->getFileName());
		$file->seek($this->reflection->getStartLine()-1);

		// Retrieve all of the lines that contain code for the closure
		$code = '';
		while ($file->key() < $this->reflection->getEndLine()) {
			$code .= $file->current();
			$file->next();
		}

		// Only keep the code defining that closure
		$begin = strpos($code, 'function');
		$end = strrpos($code, '}');
		$code = substr($code, $begin, $end - $begin + 1);

		return $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function getParameters() {
		return $this->reflection->getParameters();
	}

	protected function _fetchUsedVariables() {
		// Make sure the use construct is actually used
		$use_index = stripos($this->code, 'use');
		if (!$use_index)
			return [];

		// Get the names of the variables inside the use statement
		$begin = strpos($this->code, '(', $use_index) + 1;
		$end = strpos($this->code, ')', $begin);
		$vars = explode(',', substr($this->code, $begin, $end - $begin));

		// Get the static variables of the function via reflection
		$static_vars = $this->reflection->getStaticVariables();
	
		// Only keep the variables that appeared in both sets
		$used_vars = [];
		foreach ($vars as $var) {
			$var = trim($var, ' $&amp;');
			$used_vars[$var] = $static_vars[$var];
		}

		return $used_vars;
	}

	public function getUsedVariables() {
		return $this->used_variables;
	}

	public function __sleep() {
		return ['code', 'used_variables'];
	}

	public function __wakeup() {
		extract($this->used_variables);

		eval('$_function = '.$this->code.';');
		if (isset($_function) && $_function instanceOf Closure) {
			$this->closure = $_function;
			$this->reflection = new ReflectionFunction($_function);
		} else {
			throw new Exception();
		}
	}
}