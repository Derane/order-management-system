start:
	docker compose up -d
down:
	docker compose down
build:
	docker compose build --pull --no-cache
phpcsfixer:
	PHP_CS_FIXER_IGNORE_ENV=1 php ./vendor/bin/php-cs-fixer fix $(git ls-files -om --exclude-standard) --allow-risky=yes --config .php-cs-fixer.dist.php
psalm:
	vendor/bin/psalm
sh:
	docker exec -it order-management-system-php bash
