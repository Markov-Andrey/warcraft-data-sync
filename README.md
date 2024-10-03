# 🌟 WarCraft Data Sync — Русская версия

## 📄 Описание

**WarCraft Data Sync** — это субпроект для **WarCraft Legends**, также применимый для других проектов на платформе **WarCraft Reforged**, использующих систему каталогов. Основная задача проекта заключается в автоматическом переносе данных из главного проекта (далее — донор) в зависимые проекты (далее — реципиенты) с помощью одной команды Artisan. После переноса данных происходит очистка от технических тегов, указанных в конфигурационном файле `wts.php`.

## 🚀 Основная функциональность

1. **Синхронизация данных** из донора в несколько реципиентов.
2. **Удаление технических тегов** из файлов после копирования на основе конфигурации.
3. **Управление процессом копирования** через конфигурационные файлы:
    - **`w3x.php`** — описание файлов для копирования (название, описание, необходимость копирования).

## 📁 Структура проекта

- **/config/w3x.php** — список файлов для копирования: название, описание, необходимость переноса.
- **/storage/app/donor** — директория-источник с файлами донора.
- **/storage/app/projects/xxx.w3x** — директории реципиентов для копирования файлов и удаления тегов.

## ⚙️ Использование

1. Определите файлы для копирования в `/config/w3x.php`.
2. Паттерн удаления тегов - `[text]+many space`, например `[Thrall] `. Внимание, тег указывается в начале строки!
3. Запустите команду:

   ```bash
   php artisan build
Файлы будут автоматически скопированы в реципиентов, а технические теги удалены.

---

# 🌟 WarCraft Data Sync — English Version

## 📄 Description

**WarCraft Data Sync** is a subproject for **WarCraft Legends**, also applicable to other projects on the **WarCraft Reforged** platform that use a directory system. The main task of the project is to automatically transfer data from the main project (hereinafter referred to as the donor) to dependent projects (hereinafter referred to as recipients) using a single Artisan command. After the data is transferred, technical tags specified in the `wts.php` configuration file are removed.

## 🚀 Key Features

- Data synchronization from the donor to multiple recipients.
- Technical tag removal from files after copying, based on the configuration.
- Process management via configuration files:
    - **`w3x.php`** — file details for copying (name, description, need for copying).

## 📁 Project Structure

- **/config/w3x.php** — list of files to copy: name, description, copy necessity.
- **/storage/app/donor** — source directory containing donor files.
- **/storage/app/projects/xxx.w3x** — recipient directories for copying files and tag removal.

## ⚙️ Usage

1. Define the files to be copied in `/config/w3x.php`.
2. The pattern for removing tags is `[text] + many spaces`, for example, `[Thrall] `. Note that the tag is specified at the beginning of the line!
3. Run the command:

   ```bash
   php artisan build

Files will be automatically copied to recipients, and technical tags will be removed.
