help:																			## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:																		## install all dependencies for a development environment
	composer install

unit-tests:																		## run phpunit
	./vendor/bin/phpunit -c phpunit.xml.dist

generate-docs:																	## generate documentation
	php internal/doc_generator.php

static-analysis:																## run static analysis checks
	php phpstan.phar --configuration=phpstan.neon --memory-limit=-1

code-style:																		## run phpcs
	./vendor/bin/phpcs --basepath=. --standard=phpcs.xml

check: code-style static-analysis unit-tests
