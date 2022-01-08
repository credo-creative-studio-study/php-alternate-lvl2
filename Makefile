gendiff:
	./bin/gendiff $(args) $(file1) $(file2)

install:
	composer install

console:
	composer exec --verbose psysh

lint:
	composer exec --verbose ./vendor/bin/phpcs -- --standard=PSR12 src bin
	composer exec --verbose ./vendor/bin/phpstan -- --level=8 analyse src bin

lint-fix:
	composer exec --verbose ./vendor/bin/phpcbf -- --standard=PSR12 src tests

test:
	composer exec --verbose ./vendor/bin/phpunit tests

test-coverage:
	composer exec --verbose ./vendor/bin/phpunit tests -- --coverage-clover build/logs/clover.xml

dump autoload:
	composer dump-autoload
