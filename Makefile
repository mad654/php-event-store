usage:
	@echo "usage:"
	@echo "  make test   - runs all test"

test:
	@vendor/bin/phpunit -c tests/phpunit.xml	

test.watch:
	@watchexec -- vendor/bin/phpunit -c tests/phpunit.xml	
