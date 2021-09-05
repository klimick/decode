default: psalm-analyse phpunit-tests

psalm-analyse:
	./vendor/bin/psalm --output-format=phpstorm ./src ./psalm-test ./psalm ./test/Helper ./test/Runtime ./test/Static

phpunit-tests:
	./vendor/bin/phpunit --configuration=phpunit.xml --bootstrap=phpunit-bootstrap.php
