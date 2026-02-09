set dotenv-load := false

# Lint files
@lint:
	./vendor/bin/ecs check --fix
	./vendor/bin/php-cs-fixer fix
	./vendor/bin/rector process
	./vendor/bin/tlint lint

# Check code quality
@quality:
	./vendor/bin/phpstan analyse --memory-limit=2G

# Run unit and integration tests
@test:
	echo "Running unit and integration tests"; \
	vendor/bin/pest
