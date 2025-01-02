# DoubleDoors Plugin

DoubleDoors is a PocketMine-MP plugin that enhances the behavior of interactive blocks like doors, trapdoors, and fence gates. With this plugin, players can seamlessly open or close connected blocks of the same type, mimicking double-door mechanics and recursive opening for nearby structures.

## Features

- Automatically open or close connected blocks of the same type.
- Supports recursive opening for blocks within a configurable distance.
- Works with:
  - Doors
  - Fence Gates
  - Trapdoors
- Configurable options to enable or disable specific block types and recursive behavior.

## Installation

1. Download the latest release of the plugin.
2. Place the `.phar` file in the `plugins` folder of your PocketMine-MP server.
3. Start or restart your server.
4. A default configuration file will be generated automatically.

## Configuration

The plugin generates a `config.yml` file with the following options:

```yaml
enableRecursiveOpening: true
recursiveOpeningMaxBlocksDistance: 10
enableDoors: true
enableFenceGates: true
enableTrapdoors: true
```

- **enableRecursiveOpening**: Enable or disable recursive opening for nearby blocks.
- **recursiveOpeningMaxBlocksDistance**: Maximum distance (in blocks) for recursive opening.
- **enableDoors**: Enable or disable double-door functionality for doors.
- **enableFenceGates**: Enable or disable functionality for fence gates.
- **enableTrapdoors**: Enable or disable functionality for trapdoors.

## Usage

- Interact with a block (door, trapdoor, or fence gate) to open or close it.
- If recursive opening is enabled, nearby connected blocks of the same type will also open or close.
- Sneaking while interacting will bypass the plugin's functionality, allowing you to interact with a single block.

## Developer Notes

The plugin listens to the `PlayerInteractEvent` to detect interactions with supported blocks. It processes connected blocks recursively, ensuring only the allowed distance and block types are affected.

### Supported Block Types

- `Door`
- `FenceGate`
- `TrapDoor`

### Recursive Opening Logic

The plugin checks for nearby blocks of the same type in a 3x3x3 area around the interacted block. If recursive opening is enabled, it will continue to process connected blocks within the configured distance limit.

## Contributing

We welcome contributions! If you encounter bugs or have suggestions, feel free to open an issue or submit a pull request on GitHub.

##

