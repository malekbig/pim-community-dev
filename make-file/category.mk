.PHONY: category-lint-back
category-lint-back: #Doc: launch PHPStan for category bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Category/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-lint-fix-back
category-lint-fix-back: #Doc: launch PHPStan for category bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-end-to-end
category-end-to-end: #Doc: launch PHPUnit integration tests for category bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Category/tests --testsuite Category_EndToEnd_Test $(O)

.PHONY: category-ci-back
category-ci-back: category-lint-back category-end-to-end

.PHONY: category-ci
category-ci: category-ci-back