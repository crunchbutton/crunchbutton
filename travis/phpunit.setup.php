<?php

// outputs an xml file based on current php version

if (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 6) {
	// only do coverals on one of our tests: 5.6+
	copy('travis/phpunit.coveralls.xml', 'travis/phpunit.xml');
} else {
	copy('travis/phpunit.basic.xml', 'travis/phpunit.xml');
}

exit;