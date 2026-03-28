# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Rules

- **Never create git commits or push unless the user explicitly asks.**

## Project Overview

**WarCraft Data Sync** is a Laravel-based web UI for managing Warcraft III map projects. It synchronizes files from a parent `.w3x` map project to dependent child projects, handles file filtering/replacement rules, and compiles child projects into `.w3x` archives using an external MPQ editor tool.

There is **no database** — all state is persisted as JSON in a `.data-sync/` directory within each project path.

## Commands

```bash
# Install PHP dependencies
composer install

# Run development server (Vite)
npm run dev

# Build frontend assets
npm run build

# Start Laravel dev server
php artisan serve

# Run all tests
php artisan test

# Run a single test file
php artisan test tests/Unit/ExampleTest.php

# Run PHPUnit directly
./vendor/bin/phpunit --filter TestName
```

## Architecture

### Key Configuration Files
- **config/w3x_projects.php** — Parent project path, child project definitions, and build output paths. This is the primary config to set up.
- **config/w3x_const.php** — Lists of files to copy and exclusions/exceptions between projects.
- **config/w3x_child.php** — Maps child project identifiers to their service classes.

### Service Layer (`app/Services/`)
All business logic lives in services:
- **PathService** — Central path resolution for projects, `.data-sync` files, and the MPQ editor tool. Used everywhere — start here when tracing file paths.
- **ConfigService** — Reads/writes `files_config.json` in `.data-sync/`; builds the file tree displayed in the UI.
- **InfoConfigService** — Reads/writes `info_config.json` which tracks current active project, versions, and sync timestamps.
- **FileProcessorService** — Copies files from parent → child project based on configured rules.
- **BuildGameService** — Invokes `tools/MPQEditor/MPQEditor.exe` to package files into `.w3x` archives.
- **MapConverterService** / **BlpConverterService** — Handle `.w3x` map extraction and BLP image format conversion.
- **CrudJson** / **JsonService** — JSON CRUD utilities for the units/objects data editor.

### Data Flow
1. User selects a child project via `POST /switch-project`
2. `GET /copy-child` triggers `FileProcessorService` to sync parent → child files with filtering rules
3. `GET /set-build` triggers `BuildGameService` which shells out to `MPQEditor.exe` to create `.w3x`
4. `war3map.j` (JASS script) and `war3map.wts` (string table) are modified during the build according to replacement patterns

### Routes → Controllers
- `GET /` → `IndexPage` — renders the dashboard with file tree and project state
- `GET /copy-child` → `FileController@copyChild`
- `GET /set-build` → `FileController@setBuild`
- `POST /switch-project` → `FileController@switchProject`
- `POST /update-version` → `FileController@updateVersion`
- `GET /units` → `JsonController` — units/objects data editor
- `POST /update` → `JsonController@update`

### External Tool Dependency
`tools/MPQEditor/MPQEditor.exe` must be present for archive building to work. It is invoked via shell commands in `BuildGameService`.
