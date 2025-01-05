<?php

declare(strict_types=1);

namespace CustomAreas;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\SimpleForm;
use Vecnavium\FormsUI\CustomForm;

class Main extends PluginBase implements Listener {

    /** @var array */
    private $regions = [];

    /** @var array */
    private $playerData = [];

    /** @var Config */
    private $config;

    /** @var array */
    private $messages = [];

    /** @var array */
    private $messageShown = []; // To prevent showing the message multiple times

    /**
     * Called when the plugin is enabled.
     */
    public function onEnable() : void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->loadMessages();
        $this->loadRegions();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * Called when the plugin is disabled.
     */
    public function onDisable() : void {
        $this->saveRegions();
        $this->getLogger()->info(TextFormat::RED . "CustomAreas has been disabled.");
    }

    /**
     * Loads messages from config.yml.
     */
    private function loadMessages() : void {
        $this->messages = $this->config->get("messages", []);
    }

    /**
     * Loads regions from config.yml.
     */
    private function loadRegions() : void {
        $regions = $this->config->get("regions", []);
        foreach($regions as $name => $data){
            // Validate required keys
            if(!isset($data["min"], $data["max"], $data["world"], $data["permission"], $data["message"])){
                $this->getLogger()->warning(TextFormat::YELLOW . "Region '$name' is missing required keys. Skipping.");
                continue;
            }

            $min = $data["min"];
            $max = $data["max"];
            $world = $data["world"];
            $permission = $data["permission"];
            $message = $data["message"];

            // Check if world is loaded
            if(!$this->getServer()->getWorldManager()->isWorldLoaded($world)){
                $this->getLogger()->warning(TextFormat::YELLOW . "World '$world' for region '$name' is not loaded. Skipping.");
                continue;
            }

            $this->regions[$name] = [
                "min" => new Vector3((float)$min["x"], (float)$min["y"], (float)$min["z"]),
                "max" => new Vector3((float)$max["x"], (float)$max["y"], (float)$max["z"]),
                "world" => $world,
                "permission" => $permission,
                "message" => $message
            ];
            $this->getLogger()->info(TextFormat::YELLOW . "Loaded region: $name in world: $world");
        }
    }

    /**
     * Saves regions to config.yml.
     */
    private function saveRegions() : void {
        $regions = [];
        foreach($this->regions as $name => $data){
            $regions[$name] = [
                "min" => [
                    "x" => $data["min"]->x,
                    "y" => $data["min"]->y,
                    "z" => $data["min"]->z
                ],
                "max" => [
                    "x" => $data["max"]->x,
                    "y" => $data["max"]->y,
                    "z" => $data["max"]->z
                ],
                "world" => $data["world"],
                "permission" => $data["permission"],
                "message" => $data["message"]
            ];
        }
        $this->config->set("regions", $regions);
        $this->config->save();
    }

    /**
     * Handles commands related to CustomAreas.
     *
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if($command->getName() === "ca"){
            if(!$sender->hasPermission("customareas.command")){
                $sender->sendMessage(TextFormat::RED . $this->getMessage("no_permission"));
                return true;
            }

            if(count($args) === 0){
                if($sender instanceof Player){
                    $this->openMainForm($sender);
                } else {
                    $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
                }
                return true;
            }

            switch(strtolower($args[0])){
                case "create":
                    $this->openCreateAreaForm($sender);
                    break;
                case "edit":
                    $this->openEditAreaForm($sender);
                    break;
                case "remove":
                    $this->openRemoveAreaForm($sender);
                    break;
                case "list":
                    $this->handleList($sender);
                    break;
                case "gui":
                    $this->openMainForm($sender);
                    break;
                case "pos1":
                    $this->handlePos1($sender);
                    break;
                case "pos2":
                    $this->handlePos2($sender);
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . $this->getMessage("usage"));
                    break;
            }

            return true;
        }

        return false;
    }

    /**
     * Retrieves a message from the configuration with optional placeholders.
     *
     * @param string $key
     * @param array $placeholders
     * @return string
     */
    private function getMessage(string $key, array $placeholders = []) : string {
        if(!isset($this->messages[$key])){
            return "Missing message for key: $key";
        }

        $message = $this->messages[$key];

        // Convert placeholders to strings before replacement
        $processedPlaceholders = [];
        foreach($placeholders as $placeholder => $value){
            $processedPlaceholders['{' . $placeholder . '}'] = (string)$value;
        }

        return strtr($message, $processedPlaceholders);
    }

    /**
     * Opens the main CustomAreas GUI form.
     *
     * @param CommandSender $sender
     */
    private function openMainForm(CommandSender $sender) : void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if($data === null){
                return;
            }

            switch($data){
                case 0:
                    $this->openCreateAreaForm($player);
                    break;
                case 1:
                    $this->openEditAreaForm($player);
                    break;
                case 2:
                    $this->openRemoveAreaForm($player);
                    break;
                case 3:
                    $this->handleList($player);
                    break;
                case 4:
                    // Cancel
                    break;
            }
        });

        $form->setTitle($this->getMessage("gui_title"));
        $form->setContent($this->getMessage("gui_content"));
        foreach($this->messages["gui_buttons"] as $button){
            $form->addButton($button);
        }

        $form->sendToPlayer($sender);
    }

    /**
     * Opens the form to create a new area.
     *
     * @param CommandSender $sender
     */
    private function openCreateAreaForm(CommandSender $sender) : void {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        $form = new CustomForm(function (Player $player, ?array $data) {
            if($data === null){
                return;
            }

            // Debug the entire form data
            $this->getLogger()->debug("Form submission data: " . json_encode($data));

            // Get the area name and message
            $areaName = trim($data[1] ?? '');
            $message = trim($data[2] ?? '');

            if($areaName === ''){
                $player->sendMessage(TextFormat::RED . "Area name cannot be empty.");
                return;
            }

            $areaName = strtolower($areaName);

            if(isset($this->regions[$areaName])){
                $player->sendMessage(TextFormat::RED . $this->getMessage("region_exists", ["name" => $areaName]));
                return;
            }

            // Store data for area creation
            $this->playerData[$player->getName()] = [
                "state" => "creating",
                "area_name" => $areaName,
                "step" => "pos1",
                "world" => $player->getWorld()->getFolderName(),
                "message" => empty($message)
                    ? $this->getMessage("region_message_default", ["name" => $areaName])
                    : $message
            ];

            $player->sendMessage(TextFormat::GREEN . $this->getMessage("pos1_prompt"));
        });

        $form->setTitle($this->getMessage("gui_title"));
        $form->addLabel($this->getMessage("create_area_content")); 
        $form->addInput($this->getMessage("create_area_prompt"), "Area Name", "");
        $form->addInput(
            $this->getMessage("region_message_prompt"),
            "Region Entry Message",
            $this->getMessage("region_message_default", ["name" => "Area Name"])
        );
        $form->sendToPlayer($sender);
    }

    /**
     * Opens the form to remove an existing area.
     *
     * @param CommandSender $sender
     */
    private function openRemoveAreaForm(CommandSender $sender) : void {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        if(empty($this->regions)){
            $sender->sendMessage(TextFormat::YELLOW . $this->getMessage("list_areas_empty"));
            return;
        }

        // Store area names in array to maintain order
        $areaNames = array_keys($this->regions);

        $form = new SimpleForm(function (Player $player, ?int $data) use ($areaNames) {
            if($data === null){
                return;
            }

            if($data >= count($areaNames)){
                // Cancel button was pressed
                return;
            }

            // Get the area name from our stored array using the index
            $selectedArea = (string)$areaNames[$data];
            // Confirm deletion
            $this->confirmRemoveArea($player, $selectedArea);
        });

        $form->setTitle((string)$this->getMessage("gui_title"));
        $form->setContent((string)$this->getMessage("remove_prompt"));

        // Add area buttons
        foreach($areaNames as $areaName){
            $form->addButton((string)$areaName);
        }

        // Add cancel button
        if(isset($this->messages["gui_buttons"][4])){
            $form->addButton((string)$this->messages["gui_buttons"][4]);
        } else {
            $form->addButton("Cancel");
        }

        $form->sendToPlayer($sender);
    }

    /**
     * Confirms the removal of an area.
     *
     * @param Player $player
     * @param string $areaName
     */
    private function confirmRemoveArea(Player $player, string $areaName) : void {
        $form = new SimpleForm(function (Player $player, ?int $data) use ($areaName) {
            if($data === null){
                return;
            }

            if($data === 0){
                // Confirm removal
                if(isset($this->regions[$areaName])){
                    unset($this->regions[$areaName]);
                    $this->saveRegions();
                    $player->sendMessage(TextFormat::GREEN . $this->getMessage("remove_success", ["name" => $areaName]));
                } else {
                    $player->sendMessage(TextFormat::RED . $this->getMessage("region_not_found", ["name" => $areaName]));
                }
            }
            // Otherwise, cancel removal
        });

        $form->setTitle((string)$this->getMessage("confirm_remove_title"));
        $form->setContent((string)$this->getMessage("confirm_remove_content", ["name" => $areaName]));
        
        // Add confirm button
        if(isset($this->messages["gui_buttons"][0])){
            $form->addButton((string)$this->messages["gui_buttons"][0]);
        } else {
            $form->addButton("Confirm");
        }
        
        // Add cancel button
        if(isset($this->messages["gui_buttons"][4])){
            $form->addButton((string)$this->messages["gui_buttons"][4]);
        } else {
            $form->addButton("Cancel");
        }

        $form->sendToPlayer($player);
    }

    /**
     * Opens the form to edit an existing area.
     *
     * @param CommandSender $sender
     */
    private function openEditAreaForm(CommandSender $sender) : void {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        if(empty($this->regions)){
            $sender->sendMessage(TextFormat::YELLOW . $this->getMessage("list_areas_empty"));
            return;
        }

        $form = new SimpleForm(function (Player $player, ?int $data) {
            if($data === null){
                return;
            }

            $areaNames = array_keys($this->regions);
            if($data >= count($areaNames)){
                // Cancel button was pressed
                return;
            }

            $selectedArea = $areaNames[$data];

            // Open edit area details form
            $this->openEditAreaDetailsForm($player, $selectedArea);
        });

        $form->setTitle($this->getMessage("gui_title"));
        $form->setContent($this->getMessage("edit_prompt"));

        foreach(array_keys($this->regions) as $areaName){
            $form->addButton($areaName);
        }

        $form->addButton($this->messages["gui_buttons"][4]); // Cancel

        $form->sendToPlayer($sender);
    }

    /**
     * Opens the form to edit area details (name, message, pos1, pos2).
     *
     * @param Player $player
     * @param string $areaName
     */
    private function openEditAreaDetailsForm(Player $player, string $areaName) : void {
        $currentRegion = $this->regions[$areaName];
        $worldName = $currentRegion["world"];
        $currentMessage = $currentRegion["message"];

        $form = new CustomForm(function (Player $player, ?array $data) use ($areaName) {
            if($data === null){
                return;
            }

            $newName = strtolower(trim($data[0] ?? ''));
            $newName = empty($newName) ? $areaName : $newName;
            $newMessage = trim($data[1] ?? '');

            $changePos1 = isset($data[2]) ? (bool)$data[2] : false;
            $changePos2 = isset($data[3]) ? (bool)$data[3] : false;

            // Check if new name already exists
            if($newName !== $areaName && isset($this->regions[$newName])){
                $player->sendMessage(TextFormat::RED . $this->getMessage("region_exists", ["name" => $newName]));
                return;
            }

            // Update name if changed
            if($newName !== $areaName){
                $this->regions[$newName] = $this->regions[$areaName];
                unset($this->regions[$areaName]);
                $this->regions[$newName]["permission"] = "customareas.entry.$newName";
            }

            // Update message
            $finalName = ($newName !== $areaName) ? $newName : $areaName;
            if(!empty($newMessage)){
                $this->regions[$finalName]["message"] = $newMessage;
            } else {
                $this->regions[$finalName]["message"] =
                    $this->getMessage("region_message_default", ["name" => $finalName]);
            }

            // Handle position updates
            if($changePos1 || $changePos2){
                $this->playerData[$player->getName()] = [
                    "state" => "editing",
                    "area_name" => $finalName,
                    "step" => "pos1",
                    "change_pos1" => $changePos1,
                    "change_pos2" => $changePos2,
                    "world" => $this->regions[$finalName]["world"]
                ];
                if($changePos1){
                    $player->sendMessage(TextFormat::GREEN . $this->getMessage("pos1_prompt"));
                }
                if($changePos2){
                    $player->sendMessage(TextFormat::GREEN . $this->getMessage("pos2_prompt"));
                }
                $this->saveRegions();
                return;
            }

            // Save changes
            $this->saveRegions();
            $player->sendMessage(TextFormat::GREEN . $this->getMessage("edit_success", ["name" => $finalName]));
        });

        $form->setTitle($this->getMessage("edit_area_title"));
        $form->addLabel($this->getMessage("edit_area_content", ["name" => $areaName]));
        $form->addInput($this->getMessage("edit_area_name_prompt"), "New Area Name", $areaName);
        $form->addInput($this->getMessage("region_message_prompt"), "Region Entry Message", $currentMessage);
        $form->addToggle($this->getMessage("edit_area_change_pos1_prompt"), false);
        $form->addToggle($this->getMessage("edit_area_change_pos2_prompt"), false);
        $form->sendToPlayer($player);
    }

    /**
     * Handles the list command to display all defined areas.
     *
     * @param CommandSender $sender
     */
    private function handleList(CommandSender $sender) : void {
        if(empty($this->regions)){
            $sender->sendMessage(TextFormat::YELLOW . $this->getMessage("list_areas_empty"));
            return;
        }

        $message = TextFormat::YELLOW . $this->getMessage("list_areas");
        foreach($this->regions as $name => $data){
            $message .= "\n" . TextFormat::GREEN . "- $name " .
                TextFormat::GRAY . "(World: {$data['world']}, Permission: {$data['permission']})";
        }
        $sender->sendMessage($message);
    }

    /**
     * Handles setting position 1 for creating or editing an area.
     *
     * @param CommandSender $sender
     */
    private function handlePos1(CommandSender $sender) : void {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        if(!isset($this->playerData[$sender->getName()]["state"])){
            $sender->sendMessage(TextFormat::RED . "You are not in the process of creating or editing an area.");
            return;
        }

        $state = $this->playerData[$sender->getName()]["state"];
        $step = $this->playerData[$sender->getName()]["step"];

        if(($state === "creating" && $step !== "pos1") || ($state === "editing" && $step !== "pos1")){
            $sender->sendMessage(TextFormat::RED . "You are not setting position 1 at this moment.");
            return;
        }

        $position = $sender->getPosition();
        $this->playerData[$sender->getName()]["pos1"] = new Vector3($position->getX(), $position->getY(), $position->getZ());
        $sender->sendMessage(TextFormat::GREEN . "Position 1 set at X: {$position->getX()}, Y: {$position->getY()}, Z: {$position->getZ()}");

        if($state === "creating"){
            $sender->sendMessage(TextFormat::GREEN . $this->getMessage("pos2_prompt"));
            $this->playerData[$sender->getName()]["step"] = "pos2";
        } elseif ($state === "editing"){
            if(isset($this->playerData[$sender->getName()]["change_pos2"]) && $this->playerData[$sender->getName()]["change_pos2"]){
                $sender->sendMessage(TextFormat::GREEN . $this->getMessage("pos2_prompt"));
                $this->playerData[$sender->getName()]["step"] = "pos2";
            } else {
                // Finish editing pos1 only
                $playerName = $sender->getName();
                $areaName = $this->playerData[$playerName]["area_name"];
                $pos1 = $this->playerData[$playerName]["pos1"];
                $world = $this->playerData[$playerName]["world"];

                if(!$this->getServer()->getWorldManager()->isWorldLoaded($world)){
                    $sender->sendMessage(TextFormat::RED . "The world '$world' is not loaded. Cannot update the area.");
                    unset($this->playerData[$playerName]);
                    return;
                }

                $this->regions[$areaName]["min"] = $pos1;
                $this->saveRegions();
                $sender->sendMessage(TextFormat::GREEN . $this->getMessage("edit_pos1_success", ["name" => $areaName]));
                unset($this->playerData[$playerName]);
            }
        }
    }

    /**
     * Handles setting position 2 for creating or editing an area.
     *
     * @param CommandSender $sender
     */
    private function handlePos2(CommandSender $sender) : void {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        if(!isset($this->playerData[$sender->getName()]["state"])){
            $sender->sendMessage(TextFormat::RED . "You are not in the process of creating or editing an area.");
            return;
        }

        $state = $this->playerData[$sender->getName()]["state"];
        $step = $this->playerData[$sender->getName()]["step"];

        if(($state === "creating" && $step !== "pos2") || ($state === "editing" && $step !== "pos2")){
            $sender->sendMessage(TextFormat::RED . "You are not setting position 2 at this moment.");
            return;
        }

        if(!isset($this->playerData[$sender->getName()]["pos1"])){
            $sender->sendMessage(TextFormat::RED . "Position 1 has not been set yet.");
            return;
        }

        $position = $sender->getPosition();
        $this->playerData[$sender->getName()]["pos2"] = new Vector3($position->getX(), $position->getY(), $position->getZ());

        $playerName = $sender->getName();
        $areaName = $this->playerData[$playerName]["area_name"];
        $pos1 = $this->playerData[$playerName]["pos1"];
        $pos2 = $this->playerData[$playerName]["pos2"];
        $world = $this->playerData[$playerName]["world"];

        if(!$this->getServer()->getWorldManager()->isWorldLoaded($world)){
            $sender->sendMessage(TextFormat::RED . "The world '$world' is not loaded. Cannot create or edit the area.");
            unset($this->playerData[$playerName]);
            return;
        }

        if($state === "creating"){
            $message = $this->playerData[$playerName]["message"];
            $permission = "customareas.entry.$areaName";

            $this->regions[$areaName] = [
                "min" => $pos1,
                "max" => $pos2,
                "world" => $world,
                "permission" => $permission,
                "message" => $message
            ];

            // Save to config.yml
            $this->saveRegions();

            // Notify player
            $sender->sendMessage(TextFormat::GREEN . $this->getMessage("create_success", ["name" => $areaName]));

            // Clear player data
            unset($this->playerData[$playerName]);
        } elseif ($state === "editing"){
            $changePos2 = isset($this->playerData[$playerName]["change_pos2"]) ? $this->playerData[$playerName]["change_pos2"] : false;
            if($changePos2){
                $this->regions[$areaName]["max"] = $pos2;
                $this->saveRegions();
                $sender->sendMessage(TextFormat::GREEN . $this->getMessage("edit_pos2_success", ["name" => $areaName]));
            }

            // Clear player data
            unset($this->playerData[$playerName]);
        }
    }

    /**
     * Handles player movement to enforce region permissions and display messages.
     *
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event) : void {
        $player = $event->getPlayer();
        $to = $event->getTo();
        $world = $player->getWorld()->getFolderName();
        $playerName = $player->getName();

        foreach($this->regions as $name => $region){
            if($world !== $region["world"]){
                continue;
            }

            if($this->isInside($to, $region["min"], $region["max"])){
                // Debug log
                $this->getLogger()->info("Player $playerName entered region $name.");

                // Check permission
                if(!$player->hasPermission($region["permission"])){
                    $this->getLogger()->info("Player $playerName does NOT have permission {$region['permission']} to enter region $name.");
                    $event->cancel();
                    $player->sendMessage(TextFormat::RED . $this->getMessage("entering_region_denied", ["name" => $name]));
                    return;
                } else {
                    $this->getLogger()->info("Player $playerName has permission {$region['permission']} to enter region $name.");
                    // Display custom message
                    if(!isset($this->messageShown[$playerName][$name])){
                        $message = str_replace("{name}", $name, $region["message"]);
                        $player->sendMessage(TextFormat::GOLD . $message);
                        $this->messageShown[$playerName][$name] = true;
                    }
                }
            } else {
                // The player is not inside this region anymore, reset message
                if(isset($this->messageShown[$playerName][$name])){
                    unset($this->messageShown[$playerName][$name]);
                }
            }
        }
    }

    /**
     * Checks if a position is inside a defined region.
     *
     * @param Vector3 $pos
     * @param Vector3 $min
     * @param Vector3 $max
     * @return bool
     */
    private function isInside(Vector3 $pos, Vector3 $min, Vector3 $max) : bool {
        return $pos->x >= min($min->x, $max->x) && $pos->x <= max($min->x, $max->x) &&
               $pos->y >= min($min->y, $max->y) && $pos->y <= max($min->y, $max->y) &&
               $pos->z >= min($min->z, $max->z) && $pos->z <= max($min->z, $max->z);
    }
}
