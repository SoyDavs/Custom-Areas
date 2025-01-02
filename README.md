# CustomAreas Plugin

**CustomAreas** is a powerful PocketMine-MP plugin designed to help server administrators create and manage protected regions within their Minecraft worlds. With **CustomAreas**, you can define specific areas where only players with the appropriate permissions can enter, ensuring better control and organization of your server environments.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Commands](#commands)
- [Usage](#usage)
- [Examples](#examples)


## Features

- **Region Creation and Management:** Easily create, edit, and remove protected areas within your worlds.
- **Custom Entry Messages:** Display personalized messages to players when they enter a region.
- **Permission-Based Access:** Control access to regions using a robust permission system integrated with ChatPerms.
- **Multi-World Support:** Define regions across multiple worlds on your server.
- **User-Friendly GUI:** Utilize intuitive forms for managing regions without the need for complex commands.
- **Startup ASCII Art:** Display a stylish ASCII art message in the server logs upon startup.

## Installation

1. **Download the Plugin:**
   - Obtain the latest version of the **CustomAreas** plugin from the [official repository](#) or [PocketMine Resource Page](#).

2. **Upload to Plugins Directory:**
   - Place the downloaded `CustomAreas.phar` (or `.zip` if provided) into the `plugins/` directory of your PocketMine-MP server.

3. **Restart the Server:**
   - Restart your PocketMine-MP server to load the plugin. Upon startup, the plugin will generate a default `config.yml` file if it doesn't already exist.

## Configuration

The `config.yml` file allows you to customize messages, define regions, and set permissions. Below is an overview of the configuration options:

### `config.yml`

```yaml
# =============================================================================
# CustomAreas Plugin Configuration File
# =============================================================================
# Configures messages and regions for the CustomAreas plugin.
# Customize messages and add or modify regions as needed.
# =============================================================================

# =============================================================================
# Messages Section
# =============================================================================
messages:
  gui_title: "Custom Areas"
  gui_content: "Choose an action:"
  gui_buttons:
    - "Create Area"    # Index 0
    - "Edit Area"      # Index 1
    - "Remove Area"    # Index 2
    - "List Areas"     # Index 3
    - "Cancel"         # Index 4

  confirm_remove_title: "Confirm Removal"
  confirm_remove_content: "Are you sure you want to remove the area '{name}'?"
  
  no_permission: "You do not have permission to use this command."
  usage: "/ca <create|edit|remove|list|gui|pos1|pos2>"
  
  pos1_prompt: "Please set position 1 using /ca pos1."
  pos2_prompt: "Please set position 2 using /ca pos2."
  
  region_exists: "The region '{name}' already exists."
  
  create_success: "Region '{name}' has been created successfully."
  remove_success: "Region '{name}' has been removed successfully."
  edit_success: "Region '{name}' has been updated successfully."
  
  edit_pos1_success: "Position 1 for region '{name}' has been updated."
  edit_pos2_success: "Position 2 for region '{name}' has been updated."
  
  entering_region_denied: "You do not have permission to enter the region '{name}'."
  
  list_areas: "List of defined areas:"
  list_areas_empty: "No areas defined."
  
  edit_area_title: "Edit Area"
  edit_area_content: "Modify the area details below:"
  
  edit_area_name_prompt: "New Area Name"
  edit_area_change_pos1_prompt: "Change Position 1?"
  edit_area_change_pos2_prompt: "Change Position 2?"
  
  region_message_prompt: "Enter the custom entry message for this region:"
  region_message_default: "Welcome to {name}!"

# =============================================================================
# Regions Section
# =============================================================================
regions:
  # Example Region: Spawn
  spawn:
    min:
      x: 100      # Minimum X coordinate
      y: 64       # Minimum Y coordinate
      z: 100      # Minimum Z coordinate
    max:
      x: 200      # Maximum X coordinate
      y: 80       # Maximum Y coordinate
      z: 200      # Maximum Z coordinate
    world: world       # World name where the region is located
    permission: customareas.entry.spawn  # Permission required to enter
    message: "Welcome to Spawn!"         # Custom entry message

  # Example Region: Arena
  arena:
    min:
      x: -50      # Minimum X coordinate
      y: 70       # Minimum Y coordinate
      z: -50      # Minimum Z coordinate
    max:
      x: 50       # Maximum X coordinate
      y: 90       # Maximum Y coordinate
      z: 50       # Maximum Z coordinate
    world: arena_world  # World name where the region is located
    permission: customareas.entry.arena  # Permission required to enter
    message: "Welcome to the Arena!"       # Custom entry message

  # Add more regions following the same format:
  # my_custom_region:
  #   min:
  #     x: 300
  #     y: 70
  #     z: 300
  #   max:
  #     x: 400
  #     y: 90
  #     z: 400
  #   world: my_world
  #   permission: customareas.entry.my_custom_region
  #   message: "Welcome to My Custom Region!"
```

### Key Configuration Options

- **`messages` Section:**
  - Customize all the messages displayed by the plugin, including GUI titles, prompts, success messages, and error messages.
  - Use `{name}` as a placeholder to dynamically insert the region name into messages.

- **`regions` Section:**
  - Define each protected region with its minimum and maximum coordinates, world name, required permission, and custom entry message.
  - Ensure that the world specified is loaded on your server.

### Adding New Regions

To add a new region, follow the existing structure under the `regions` section. For example:

```yaml
my_custom_region:
  min:
    x: 300
    y: 70
    z: 300
  max:
    x: 400
    y: 90
    z: 400
  world: my_world
  permission: customareas.entry.my_custom_region
  message: "Welcome to My Custom Region!"
```

## Commands

The **CustomAreas** plugin provides several commands to manage regions. Below are the available commands and their descriptions:

- **`/ca`**
  - Opens the main GUI for managing regions.
  - **Usage:** `/ca`

- **`/ca create`**
  - Initiates the process to create a new region.
  - **Usage:** `/ca create`

- **`/ca edit`**
  - Opens the GUI to edit an existing region.
  - **Usage:** `/ca edit`

- **`/ca remove`**
  - Opens the GUI to remove an existing region.
  - **Usage:** `/ca remove`

- **`/ca list`**
  - Lists all defined regions.
  - **Usage:** `/ca list`

- **`/ca gui`**
  - Opens the main GUI for managing regions.
  - **Usage:** `/ca gui`

- **`/ca pos1`**
  - Sets Position 1 for creating or editing a region.
  - **Usage:** `/ca pos1`

- **`/ca pos2`**
  - Sets Position 2 for creating or editing a region.
  - **Usage:** `/ca pos2`

## Permissions

Permissions control access to various commands and regions. Integrate **CustomAreas** with your permission management plugin (e.g., ChatPerms) to assign these permissions to players or groups.

### Command Permissions

- **`customareas.command`**
  - **Description:** Allows the use of all **CustomAreas** commands.
  - **Usage:** Assign this permission to players or groups that should have access to manage regions.
  

### Region Entry Permissions

Each region has a unique permission node that controls who can enter it. These permissions are defined in the `config.yml` under each region.

- **`customareas.entry.<region_name>`**
  - **Description:** Allows entry into the specified region.
  - **Usage:** Assign this permission to players or groups that should have access to the region.
  


## Usage

1. **Accessing the GUI:**
   - Use the `/ca` or `/ca gui` command to open the main **CustomAreas** GUI.

2. **Creating a New Region:**
   - Click on the "Create Area" button in the GUI.
   - Enter the region name and a custom entry message.
   - Follow the prompts to set Position 1 and Position 2 by using `/ca pos1` and `/ca pos2` while standing in the desired locations.

3. **Editing an Existing Region:**
   - Click on the "Edit Area" button in the GUI.
   - Select the region you want to edit.
   - Modify the region name, entry message, and choose whether to change Position 1 or Position 2.

4. **Removing a Region:**
   - Click on the "Remove Area" button in the GUI.
   - Select the region you wish to remove and confirm the deletion.

5. **Listing All Regions:**
   - Use the `/ca list` command to view all defined regions along with their details.

6. **Entering a Region:**
   - When a player enters a defined region, the plugin checks if they have the necessary permission.
   - If permitted, the player sees the custom entry message.
   - If not, their movement is canceled, and they receive a denial message.

## Examples

### Creating a Region Named "Market"

1. **Open the GUI:**
   ```bash
   /ca
   ```

2. **Select "Create Area":**
   - Click on "Create Area".

3. **Enter Details:**
   - **Area Name:** `market`
   - **Entry Message:** `Welcome to the Market!`

4. **Set Positions:**
   - Stand in the desired Position 1 and use:
     ```bash
     /ca pos1
     ```
   - Stand in the desired Position 2 and use:
     ```bash
     /ca pos2
     ```

5. **Assign Permissions:**
   ```bash
   /cp addgroupperm vip customareas.entry.market
   ```

### Editing the "Market" Region

1. **Open the GUI:**
   ```bash
   /ca
   ```

2. **Select "Edit Area":**
   - Click on "Edit Area".

3. **Choose "market":**
   - Select the "market" region.

4. **Modify Details:**
   - **New Area Name:** `central_market` (or leave blank to keep the same)
   - **Entry Message:** `Welcome to the Central Market!`
   - **Change Position 1:** Yes/No
   - **Change Position 2:** Yes/No

### Removing the "Central Market" Region

1. **Open the GUI:**
   ```bash
   /ca
   ```

2. **Select "Remove Area":**
   - Click on "Remove Area".

3. **Choose "central_market":**
   - Select the "central_market" region.

4. **Confirm Removal:**
   - Confirm the deletion when prompted.



---

**CustomAreas** enhances your PocketMine-MP server by providing a flexible and secure way to manage protected regions. Empower your server administration with precise control and personalized player experiences. Happy crafting!
