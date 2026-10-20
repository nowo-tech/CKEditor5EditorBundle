# CKEditor 5 Editor Bundle — development (Docker + pnpm + PHPUnit).
.PHONY: help up down build shell install test test-coverage coverage-php-percent cs-check cs-fix qa clean assets assets-build assets-watch test-ts ensure-up rector rector-dry phpstan igor release-check release-check-demos composer-sync update validate validate-translations check-no-cursor-coauthor check-open-prs demo-smoke strip-cursor-coauthor-from-history setup-hooks check-twig-extra check-no-raw-html-form twig-lint assets-watch

COMPOSE_FILE ?= docker-compose.yml
# Prefer Compose V2 plugin (GitHub Actions / modern Docker Desktop); fall back to docker-compose V1 (REQ-MAKE-010).
COMPOSE_BIN ?= $(shell docker compose version >/dev/null 2>&1 && echo "docker compose" || echo "docker-compose")
COMPOSE     ?= $(COMPOSE_BIN) -f $(COMPOSE_FILE)
SERVICE_PHP ?= php

help:
	@echo "CKEditor 5 Editor Bundle - Development Commands"
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  up            Start Docker container (composer + pnpm install)"
	@echo "  down          Stop Docker container"
	@echo "  build         Rebuild Docker image (no cache)"
	@echo "  shell         Open shell in container"
	@echo "  install       Install Composer and pnpm dependencies"
	@echo "  assets        Build Vite/TS assets (pnpm install + build)"
	@echo "  test          Run PHPUnit tests"
	@echo "  test-coverage Run PHPUnit with code coverage"
	@echo "  test-ts       Run Vitest with coverage"
	@echo "  cs-check      Check code style"
	@echo "  cs-fix        Fix code style"
	@echo "  rector        Apply Rector refactoring"
	@echo "  rector-dry    Run Rector in dry-run mode"
	@echo "  phpstan       Run PHPStan static analysis"
	@echo "  igor          Run Igor worker-state audit (REQ-CS-008)"
	@echo "  qa            Run all QA checks (cs-check + test)"
	@echo "  release-check Pre-release pipeline (checks, CS, Rector, PHPStan, Igor, coverage, TS tests, demos)"
	@echo "  composer-sync Validate composer.json and align composer.lock (no install)"
	@echo "  clean         Remove vendor, node_modules and caches"
	@echo "  update        Update composer.lock (composer update)"
	@echo "  update-deps   Update bundle and demo dependencies (REQ-MAKE-008)"
	@echo "  validate      Run composer validate --strict"
	@echo ""
	@echo "Bundle-specific:"
	@echo "  assets-watch  Rebuild assets on change"
	@echo "  validate-translations Check translation YAML key parity (REQ-MAKE-004)"
	@echo "  twig-lint     Lint Twig form themes"
	@echo "  check-twig-extra / check-no-raw-html-form  Twig gates (REQ-TWIG-004 / REQ-TWIG-005)"
	@echo "  check-open-prs Fail if unresolved open GitHub PRs remain (REQ-REL-003)"
	@echo "  demo-smoke    Boot the demo and assert HTTP 200 (REQ-TEST-011)"
	@echo "  setup-hooks   Install git hooks"
	@echo ""
	@echo "Demos: make -C demo (see demo/README.md) — /demo/variants for heights & themes"
	@echo ""

build:
	$(COMPOSE) build --no-cache

up:
	$(COMPOSE) build
	$(COMPOSE) up -d
	@sleep 3
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction
	$(COMPOSE) exec -T -e CI=true $(SERVICE_PHP) pnpm install
	@echo "Container ready."

down:
	$(COMPOSE) down

ensure-up:
	@if ! $(COMPOSE) exec -T $(SERVICE_PHP) true 2>/dev/null; then \
		$(COMPOSE) up -d; sleep 3; \
		$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction; \
		$(COMPOSE) exec -T -e CI=true $(SERVICE_PHP) pnpm install; \
	fi

shell:
	$(COMPOSE) exec $(SERVICE_PHP) sh

install: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install
	$(COMPOSE) exec -T -e CI=true $(SERVICE_PHP) pnpm install

assets: ensure-up
	$(COMPOSE) exec -T -e CI=true $(SERVICE_PHP) pnpm install
	$(COMPOSE) exec -T $(SERVICE_PHP) pnpm run build
	@echo "Assets: src/Resources/public/ckeditor5-editor.js"

assets-build: assets

assets-watch: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) pnpm run watch

test-ts: ensure-up
	$(COMPOSE) exec -T -e CI=true $(SERVICE_PHP) pnpm install --no-frozen-lockfile 2>/dev/null || true
	$(COMPOSE) exec -T $(SERVICE_PHP) pnpm run test:coverage | tee coverage-ts.txt
	sh .scripts/ts-coverage-percent.sh coverage-ts.txt
assets-test: test-ts

test: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction
	$(COMPOSE) exec $(SERVICE_PHP) composer test

test-coverage: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction
	$(COMPOSE) exec $(SERVICE_PHP) composer test-coverage | tee coverage-php.txt
	sh .scripts/php-coverage-percent.sh coverage-php.txt

cs-check: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-check

cs-fix: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-fix

rector: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer rector

rector-dry: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer rector-dry

phpstan: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer phpstan

validate-translations: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) php .scripts/validate-translations.php

qa: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer qa

composer-sync: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict
	$(COMPOSE) exec -T $(SERVICE_PHP) composer update --lock --no-install --no-interaction

# REQ-GIT-001 — see docs/GITHUB_CI.md
check-no-cursor-coauthor:
	@chmod +x .scripts/check-no-cursor-coauthor.sh
	@./.scripts/check-no-cursor-coauthor.sh HEAD

# REQ-REL-003 — no unresolved open GitHub PRs before release
check-open-prs:
	@chmod +x .scripts/check-open-prs.sh
	@bash .scripts/check-open-prs.sh

# REQ-TEST-011 — demo boots and returns HTTP 200 (delegates to demo/release-verify)
demo-smoke:
	@$(MAKE) -C demo release-verify

strip-cursor-coauthor-from-history:
	@chmod +x .scripts/strip-cursor-coauthor-from-history.sh
	@./.scripts/strip-cursor-coauthor-from-history.sh main

setup-hooks:
	@chmod +x .githooks/commit-msg 2>/dev/null || true
	@git config core.hooksPath .githooks
	@echo "Git hooks installed (.githooks — includes commit-msg for REQ-GIT-001)."


check-twig-extra:
	@chmod +x .scripts/check-twig-extra.sh
	@./.scripts/check-twig-extra.sh

# REQ-TWIG-005 — no raw <form>/<input> markup in bundle views or demo templates
check-no-raw-html-form:
	@chmod +x .scripts/check-no-raw-html-form.sh
	@./.scripts/check-no-raw-html-form.sh

# Run Igor worker-state audit (REQ-CS-008)
igor: ensure-up
	$(COMPOSE) exec -T php composer igor
release-check: ensure-up check-no-cursor-coauthor check-open-prs check-twig-extra check-no-raw-html-form validate-translations composer-sync cs-fix cs-check rector-dry phpstan igor test-coverage test-ts release-check-demos

release-check-demos:
	@if [ -d demo ]; then $(MAKE) -C demo release-check; fi

clean:
	rm -rf vendor node_modules .phpunit.cache coverage .php-cs-fixer.cache coverage-php.txt coverage-ts.txt

update: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer update --no-interaction

validate: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict

# REQ-MAKE-008: update-deps (REQ-MAKE-008)
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.update-deps.mk

twig-lint: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer twig:lint || $(COMPOSE) exec -T $(SERVICE_PHP) ./vendor/bin/twig-cs-fixer lint --config=.twig-cs-fixer.php
