# ItemFrameDupe
[![GitHub license](https://img.shields.io/github/license/ninjaknights/ItemFrameDupe)](https://github.com/ninjaknights/ItemFrameDupe/blob/main/LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/ninjaknights/ItemFrameDupe)](https://github.com/ninjaknights/ItemFrameDupe/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/ninjaknights/ItemFrameDupe)](https://github.com/ninjaknights/ItemFrameDupe/network/members)
[![GitHub issues](https://img.shields.io/github/issues/ninjaknights/ItemFrameDupe)](https://github.com/ninjaknights/ItemFrameDupe/issues)
[![Github downloads](https://img.shields.io/github/downloads/ninjaknights/ItemFrameDupe/total)](https://github.com/ninjaknights/ItemFrameDupe/releases)

<p align="center">
	<a href="https://github.com/ninjaknights/ItemFrameDupe">
    <img src="icon.png?raw=true" alt="ItemFrameDupe Icon" width="150" /></a><br>
	<b>ItemFrameDupe</b> is a PocketMine-MP plugin designed to duplicate items in item-frames in the world.
	ported from the Java Plugin <b>FrameDupe by MrRafter</b>
	<br>
	<a href="https://github.com/MrRafter/FrameDupe">View on GitHub (Frame Dupe)</a>
</p>

## 📖 How to use
1. **Install the Plugin**: Download the latest release
2. **Place the Plugin**: Move the downloaded `.phar` file into your PocketMine `plugins` directory.
3. **Start the Server**: Launch your PocketMine server.
4. **Place an Item Frame** in the world.
5. **Add a item** to the item frame.
6. **Sneak and right-click** the item frame to duplicate the item inside it.
7. **Enjoy** your duplicated items!

## 🛠️ Config

```yaml
# ItemFrameDupe Configuration

# This is the configuration file for ItemFrameDupe.
# You can customize the plugin's behavior by modifying the settings below.
# The plugin is designed to duplicate items in item frames when the player sneaks and right-clicks the frame.

# Enable or disable the plugin
enabled: true

# If set to true, players need the permission 'iframedupe.use' to use the dupe feature.
# If set to false, the dupe feature is available to all players.
need-permission: false

# Rotation-based duplication chances (0-100)
# there are total of 8 rotations (0-7)
# Each rotation has a different chance of duplication.
# Format: rotation: chance
# Example: 0: 100 means rotation_0 has a 100% chance of duplication, while others have 0%.
# Adjust the chances according to your needs.
chances:
  rotation_0: 100
  rotation_1: 0
  rotation_2: 0
  rotation_3: 0
  rotation_4: 0
  rotation_5: 0
  rotation_6: 0
  rotation_7: 0

# Enable or disable dupe logging
# logged in the dupe.log file
log-dupes: true

# Enable or disable shulker box duplication
# If set to true, shulker boxes can be duplicated.
dupe-shulker: true

# Enable or disable if it should dupe enchanted items
# If set to true, items can be duplicated even if they are enchanted.
allow-enchanted: true

# Cooldown settings
# Cooldown in seconds for all items if not specified in item-cooldowns
default-cooldown: 60

# Cooldown settings for specific items
# Format: item_name: cooldown_seconds
# example: diamond_block: 100 means diamond blocks have a cooldown of 100 seconds.
# If an item is not specified here, it will use the default cooldown.
item-cooldowns:
  diamond_block: 100
  diamond: 60
  nether_star: 10
  gold_block: 5

```

## 📄 License

This project is licensed under the [GNU License](https://github.com/ninjaknights/ItemFrameDupe/blob/main/LICENSE).

---

## 📬 Contact

Have questions or need help? Join out [Discord](https://discord.gg/ZKfh5ycJrU) Server or Join the conversation in the [issues section](https://github.com/ninjaknights/ItemFrameDupe/issues).
