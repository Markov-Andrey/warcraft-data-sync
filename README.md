# WarCraft Data Sync

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
- **PHP**: 8.5
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

3. Configure variables in `config/w3x_projects.php`, including paths for the parent project and build output for child projects.

   Example:

    ```ini
    parent_project      =   path//to//parent//project
    child_projects      =   path//to//child//projects
    build_output_path   =   path//to//build//output
    ```

---

## Configuration

The project uses several configuration files to adjust its behavior:

### `config/w3x_projects.php`

This file contains all the main variables for the path to the parent project and the directory for building child projects:

- `parent_project`: Path to the parent project.
- `child_projects`: Path to the child project directories.
- `build_output_path`: Path for saving the built archives.

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

1. Specify the paths to the parent project and child projects in `config/w3x_projects.php`.
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

## Warcraft III Map File Reference

This is a reference of important map files and their purposes.

| File Name         | Description                                                                      |
|-------------------|----------------------------------------------------------------------------------|
| war3map.j         | Code                                                                             |
| war3map.w3e       | Terrain texturing                                                                |
| war3map.shd       | Shadow map                                                                       |
| war3map.wpm       | Passability map                                                                  |
| war3map.doo       | Info about trees                                                                 |
| war3mapUnits.doo  | Information about all objects placed on map                                      |
| war3map.w3i       | Various information set in the editor in the scenario section                    |
| war3map.wts       | String values (TRIGSTR)                                                          |
| war3mapMap.blp    | Minimap                                                                          |
| war3map.mmp       | Minimap icons during initialization                                              |
| war3map.w3u       | Units - Objects                                                                  |
| war3map.wtg       | Trigger and variable names                                                       |
| war3map.w3c       | Camera parameters                                                                |
| war3map.w3r       | Info by regions                                                                  |
| war3map.w3s       | Sounds are set                                                                   |
| war3map.wct       | Map script + any text script                                                     |
| war3map.imp       | Contains info about imported files                                               |
| war3mapMisc.txt   | Constants                                                                        |
| war3mapExtra.txt  | Editor settings                                                                  |
| war3map.w3a       | Abilities - Objects                                                              |
| war3map.w3b       | Destructables - Objects                                                          |
| war3map.w3d       | Doodads - Objects                                                                |
| war3map.w3h       | Buffs - Objects                                                                  |
| war3map.w3q       | Upgrades - Objects                                                               |
| war3map.w3t       | Items - Objects                                                                  |
| war3mapSkin.w3a   | Skin data - Abilities                                                            |
| war3mapSkin.w3b   | Skin data - Destructables                                                        |
| war3mapSkin.w3d   | Skin data - Doodads                                                              |
| war3mapSkin.w3h   | Skin data - Buffs                                                                |
| war3mapSkin.w3q   | Skin data - Upgrades                                                             |
| war3mapSkin.w3t   | Skin data - Items                                                                |
| war3mapSkin.w3u   | Skin data - Units                                                                |
| conversation.json | Needed to match portrait talking animations to their respective sound file lines |
