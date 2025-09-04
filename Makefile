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
	docker compose exec php sh
codecept:
	docker compose exec php vendor/bin/codecept run Functional OrderApiCest -v
run-consumer:
	docker compose exec php bin/console messenger:consume async -vv

api-help: ## Show API development help
	@echo "API Development Commands:"
	@echo "  setup-api   - Setup API (install deps + migrate)"
	@echo "  migrate-api - Run database migrations"
	@echo "  worker-api  - Start messenger worker for emails"
	@echo "  serve-api   - Start development server"
	@echo "  test-api    - Test API endpoints manually"

migrate-api: ## Run database migrations
	docker compose exec php bin/console doctrine:migrations:migrate --no-interaction

test-setup: ## Setup test environment
	@echo "Setting up test environment..."
	docker compose exec php php bin/console doctrine:database:drop --force --if-exists --env=test
	docker compose exec php php bin/console doctrine:database:create --env=test
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --env=test
