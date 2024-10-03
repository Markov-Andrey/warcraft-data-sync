# 🌟 WarCraft Data Sync — Русская версия

## 📄 Описание

**WarCraft Data Sync** — это субпроект для **WarCraft Legends**, также применимый для других проектов на платформе **WarCraft Reforged**, использующих систему каталогов. Основная задача проекта заключается в автоматическом переносе данных из главного проекта (далее — донор) в зависимые проекты (далее — реципиенты) с помощью одной команды Artisan. После переноса данных происходит очистка от технических тегов, указанных в конфигурационном файле `wts.php`.

## 🚀 Основная функциональность

1. **Синхронизация данных** из донора в несколько реципиентов.
2. **Удаление технических тегов** из файлов после копирования на основе конфигурации.
3. **Управление процессом копирования** через конфигурационные файлы:
    - **`w3x.php`** — описание файлов для копирования (название, описание, необходимость копирования).
    - **`wts.php`** — список технических тегов для удаления.

## 📁 Структура проекта

- **/config/w3x.php** — список файлов для копирования: название, описание, необходимость переноса.
- **/config/wts.php** — список технических тегов, которые будут удалены после копирования.
- **/storage/app/donor** — директория-источник с файлами донора.
- **/storage/app/projects/xxx.w3x** — директории реципиентов для копирования файлов и удаления тегов.

## ⚙️ Использование

1. Определите файлы для копирования в `/config/w3x.php`.
2. Определите теги для удаления в `/config/wts.php`.
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
    - **`wts.php`** — list of technical tags for removal.

## 📁 Project Structure

- **/config/w3x.php** — list of files to copy: name, description, copy necessity.
- **/config/wts.php** — list of technical tags to be removed after copying.
- **/storage/app/donor** — source directory containing donor files.
- **/storage/app/projects/xxx.w3x** — recipient directories for copying files and tag removal.

## ⚙️ Usage

1. Define the files to copy in `/config/w3x.php`.
2. Define the tags to remove in `/config/wts.php`.
3. Run the command:

   ```bash
   php artisan build

Files will be automatically copied to recipients, and technical tags will be removed.
