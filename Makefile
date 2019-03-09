usage:
	@echo "usage:"
	@echo "  make test       - runs all test"
	@echo "  make test.watch - watch all test"

test: test.unit test.performance

test.unit.coverage:
	@vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit \
		--coverage-html ./build/log/report \
		--coverage-clover ./build/log/coverage.xml \
		--testdox-text ./build/log/testdox.txt \
		--testdox-html ./build/log/testdox.html

test.unit:
	@echo "Unit tests:"
	@vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit

test.performance:
	@echo "Performance tests:"
	@vendor/bin/phpunit -c tests/phpunit.xml --testsuite performance --testdox

test.watch:
	@watchexec --verbose -- vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit
