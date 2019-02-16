usage:
	@echo "usage:"
	@echo "  make test       - runs all test"
	@echo "  make test.watch - watch all test"

test:
	@vendor/bin/phpunit -c tests/phpunit.xml tests/

test.watch:
	@watchexec -- vendor/bin/phpunit -c tests/phpunit.xml tests/
