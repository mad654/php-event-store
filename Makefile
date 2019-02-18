usage:
	@echo "usage:"
	@echo "  make test       - runs all test"
	@echo "  make test.watch - watch all test"

test:
	@echo "Unit tests:"
	@vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit
	@echo "Performance tests:"
	@vendor/bin/phpunit -c tests/phpunit.xml --testsuite performance --testdox

test.watch:
	@watchexec -- vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit
