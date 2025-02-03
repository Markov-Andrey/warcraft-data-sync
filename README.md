# WarCraft Data Sync (ENG)

**WarCraft Data Sync** is a system designed for transferring data from a parent project to dependent projects. It analyzes the file structure and allows you to configure which files and directories should be copied and which ones should not. The system also allows you to:

- Specify files and directories unique to the dependent project.
- Clean up technical tags in the `war3map.wts` file.
- Make changes to the `war3map.j` file according to a specified pattern.
- Package child projects into `.w3x` archives, including all changes and versions.
- Transfer the map of a child project to the parent project.

---

## Tech Stack

- **Backend**: Laravel 8.x
- **Frontend**: Blade
- **PHP**: 8.1
- **No Database**: Uses file structure and `.data-sync` directory to store all data and metadata.
- **Archives**: `.w3x` — the archive format for building final packs.

---

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/Markov-Andrey/warcraft-data-sync.git
    cd warcraft-data-sync
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

3. Copy `.env.example` to `.env` and set the paths for the parent and child projects:

    ```bash
    cp .env.example .env
    ```

4. Configure variables in `.env`, including paths for the parent project and build output for child projects.

   Example:

    ```ini
    PARENT_PROJECT_PATH=/path/to/parent/project
    CHILD_PROJECTS_PATH=/path/to/child/projects
    BUILD_PATH=/path/to/build/output
    ```

---

## Configuration

The project uses several configuration files to adjust its behavior:

### `.env`

This file contains all the main variables for the path to the parent project and the directory for building child projects:

- `PARENT_PROJECT_PATH`: Path to the parent project.
- `CHILD_PROJECTS_PATH`: Path to the child project directories.
- `BUILD_PATH`: Path for saving the built archives.

### `config/w3x_const.php`

This file contains constants that manage file copying restrictions and which files should be copied to the parent project during the switch.

### `config/w3x_replace.php`

This file contains replacement rules for the project. It defines patterns for replacing text or strings in files and what those patterns should be replaced with.

---

## Web Interface

The project includes a web interface built with **Laravel** and **Blade**. The interface allows you to:

- **Switch**: The "Current Project" setting is used for switching between different child projects.
- **Versions**: The "Build Version" setting adds a version suffix to the built archive (e.g., `<title>-<version>.w3x`).

---

## Running the Server

1. Make sure **PHP 8.1** is installed.
2. Start the server:

    ```bash
    php artisan serve
    ```

The server will be available at `http://localhost:8000`.

---

## How It Works

**WarCraft Data Sync** performs several operations for each child project:

1. **File Structure Analysis**: The system analyzes files and directories in the project using the configuration from `.env` and `config/w3x_const.php`.
2. **File Filtering**: Determines which files should be copied based on the settings.
3. **Auto Replacement**: Applies replacements according to the rules in `config/w3x_replace.php`.
4. **Tag Cleanup**: Cleans technical tags from the `war3map.wts` file if required.
5. **Switch**: The "Current Project" setting allows you to switch between child projects.
6. **Archive Build**: All changes are compiled into a `.w3x` archive that includes all necessary files and modifications.

---

## Example Usage

1. Specify the paths to the parent project and child projects in `.env`.
2. Set the parameters in the web interface:
    - Choose the parent project.
    - Choose the child project.
    - Specify the build version.
3. Click **"Build"** to create the `.w3x` archive with the selected version.

---

## Important Files and Directories

- **.data-sync**: The directory that acts as a database. It stores all metadata about the last operation as well as the file structure.
- **.env**: The configuration file where paths to projects and other variables are defined.
- **config/w3x_const.php**: The file with constants to control the copy blocking settings.
- **config/w3x_replace.php**: The file for text replacements within the project.

---

# WarCraft Data Sync (RU)

**WarCraft Data Sync** — это система для переноса данных от родительского проекта к зависимым проектам. Она анализирует файловую структуру и позволяет настраивать, какие файлы и директории должны быть скопированы, а какие нет. Также система позволяет:

- Указывать файлы и директории, уникальные для зависимого проекта.
- Очищать технические теги в файле `war3map.wts`.
- Вносить изменения в файл `war3map.j` согласно заданному паттерну.
- Собрать дочерние проекты в архив формата `.w3x`, который включает все изменения и версии.
- Переносить карту дочернего проекта в родительский.

---

## Стек технологий

- **Backend**: Laravel 8.x
- **Frontend**: Blade
- **PHP**: 8.1
- **Без базы данных**: Использует файловую структуру и директорию `.data-sync` для хранения всех данных и метаданных.
- **Архивы**: `.w3x` — формат архива для сборки финальных паков.

---

## Установка

1. Клонируйте репозиторий:

    ```bash
    git clone https://github.com/Markov-Andrey/warcraft-data-sync.git
    cd warcraft-data-sync
    ```

2. Установите зависимости:

    ```bash
    composer install
    ```

3. Скопируйте файл `.env.example` в `.env` и настройте пути к родительским и дочерним проектам:

    ```bash
    cp .env.example .env
    ```

4. Настройте переменные в `.env`, включая пути к родительскому проекту и сборке для дочерних проектов.

   Пример:

    ```ini
    PARENT_PROJECT_PATH=/path/to/parent/project
    CHILD_PROJECTS_PATH=/path/to/child/projects
    BUILD_PATH=/path/to/build/output
    ```

---

## Конфигурация

Проект использует несколько конфигурационных файлов для настройки работы:

### `.env`

В этом файле указаны все основные переменные для пути к родительскому проекту и директории для сборки дочерних проектов:

- `PARENT_PROJECT_PATH`: Путь к родительскому проекту.
- `CHILD_PROJECTS_PATH`: Путь к директориям дочерних проектов.
- `BUILD_PATH`: Путь для сохранения собранных билдов.

### `config/w3x_const.php`

Этот файл содержит константы, которые управляют блокировкой копирования файлов и указанием файлов, которые должны копироваться в родительский проект при свиче.

### `config/w3x_replace.php`

Этот файл содержит правила автозамены для проекта. В нем задаются паттерны для замены текста или строк в файлах, а также на что эти паттерны заменяются.

---

## Веб-интерфейс

Проект включает веб-интерфейс на основе **Laravel** и **Blade**. Интерфейс позволяет:

- **Switch**: Параметр "Current Project" используется для выполнения свича между различными дочерними проектами.
- **Versions**: Параметр "Build Version" добавляет постфикс версии в собранный архив (например, `<title>-<version>.w3x`).

---

## Запуск сервера

1. Убедитесь, что у вас установлен **PHP 8.1**.
2. Запустите сервер:

    ```bash
    php artisan serve
    ```

Сервер будет доступен по адресу `http://localhost:8000`.

---

## Описание работы

**WarCraft Data Sync** выполняет несколько операций для каждого дочернего проекта:

1. **Анализ файловой структуры**: Система анализирует файлы и директории в проекте, используя конфигурацию из `.env` и `config/w3x_const.php`.
2. **Фильтрация файлов**: Определяет, какие файлы должны быть скопированы в зависимости от настроек.
3. **Автозамена**: Применяет автозамены в соответствии с правилами из `config/w3x_replace.php`.
4. **Очищение тегов**: Очищает технические теги из файла `war3map.wts`, если это предусмотрено.
5. **Свич**: Параметр "Current Project" позволяет переключаться между дочерними проектами.
6. **Сборка архива**: Все изменения собираются в архив `.w3x`, который включает все необходимые файлы и изменения.

---

## Пример использования

1. Укажите в `.env` пути к родительскому проекту и дочерним проектам.
2. Настройте параметры в веб-интерфейсе:
    - Выберите родительский проект.
    - Выберите дочерний проект.
    - Укажите версию билда.
3. Нажмите **"Собрать"** для создания архива `.w3x` с нужной версией.

---

## Важные файлы и директории

- **.data-sync**: Директория, которая выполняет роль базы данных. Хранит все метаданные о последней операции, а также архитектуру файлов.
- **.env**: Конфигурационный файл, где задаются пути к проектам и другие переменные.
- **config/w3x_const.php**: Файл с константами для настройки блокировки копирования.
- **config/w3x_replace.php**: Файл для автозамены в проекте.

---

