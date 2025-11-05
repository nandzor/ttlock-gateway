.PHONY: deploy dev local staging production

# Ensure PATH includes /usr/bin for docker commands
export PATH := /usr/bin:/usr/local/bin:$(PATH)

deploy:
	docker-compose up -d

dev:
	docker build . -t ttlock/dashboard:dev -f docker/frankenphp/Dockerfile
	docker compose -f docker-compose.dev.yaml up -d

local:
	docker build . -t ttlock/dashboard:local -f docker/frankenphp/Dockerfile
	docker compose -f docker-compose.local.yaml up -d

staging:
	docker build . -t ttlock/dashboard:staging -f docker/frankenphp/Dockerfile
	docker compose -f docker-compose.staging.yaml up -d

production:
	docker build . -t ttlock/dashboard:latest -f docker/frankenphp/Dockerfile
	docker-compose -f docker-compose.yaml up -d

