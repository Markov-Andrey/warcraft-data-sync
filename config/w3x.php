<?php

return [
    'files' => [
        'war3map.j'           => ['copy' => true],  // code
        'war3map.w3e'         => ['copy' => false], // terrain texturing
        'war3map.shd'         => ['copy' => false], // shadow map
        'war3map.wpm'         => ['copy' => false], // passability map
        'war3map.doo'         => ['copy' => false], // info about trees
        'war3mapUnits.doo'    => ['copy' => false], // information about all objects placed on map
        'war3map.w3i'         => ['copy' => false],  // Various information about the map, which is set in the editor in the scenario section
        'war3map.wts'         => ['copy' => true],  // String values (GUI)
        'war3mapMap.blp'      => ['copy' => false], // Minimap
        'war3map.mmp'         => ['copy' => false], // Minimap icons during initialization
        'war3map.w3u'         => ['copy' => true],  // Units - Objects
        'war3map.wtg'         => ['copy' => true],  // Trigger and variable names
        'war3map.w3c'         => ['copy' => false], // Camera parameters
        'war3map.w3r'         => ['copy' => false], // Info by regions
        'war3map.w3s'         => ['copy' => false], // Sounds are set
        'war3map.wct'         => ['copy' => true],  // map script + any text script
        'war3map.imp'         => ['copy' => true],  // contains info about imported files
        'war3mapMisc.txt'     => ['copy' => true],  // constants
        'war3mapExtra.txt'    => ['copy' => true],  // editor settings
        'war3map.w3a'         => ['copy' => true],  // Abilities - Objects
        'war3map.w3b'         => ['copy' => true],  // Destructables - Objects
        'war3map.w3d'         => ['copy' => true],  // Doodads - Objects
        'war3map.w3h'         => ['copy' => true],  // Buffs - Objects
        'war3map.w3q'         => ['copy' => true],  // Upgrades - Objects
        'war3map.w3t'         => ['copy' => true],  // Items - Objects

        'war3mapSkin.w3a'     => ['copy' => true],  // Skin data - Abilities
        'war3mapSkin.w3b'     => ['copy' => true],  // Skin data - Destructables
        'war3mapSkin.w3d'     => ['copy' => true],  // Skin data - Doodads
        'war3mapSkin.w3h'     => ['copy' => true],  // Skin data - Buffs
        'war3mapSkin.w3q'     => ['copy' => true],  // Skin data - Upgrades
        'war3mapSkin.w3t'     => ['copy' => true],  // Skin data - Items
        'war3mapSkin.w3u'     => ['copy' => true],  // Skin data - Units

        'conversation.json'   => ['copy' => false], // are needed to match portrait talking animations to their respective sound file lines

        // my assets in root folder
        // TODO Refactor required!
        'AzureGlow4.blp'        => ['copy' => true],
        'AzureGlow5.blp'        => ['copy' => true],
        'CrimsonGlow2.blp'      => ['copy' => true],
        'damned_7x7_sprite.blp' => ['copy' => true],
        'FullScreen.blp'        => ['copy' => true],
        'LoadingScreen.blp'     => ['copy' => true],
        'RadiantGlow.blp'       => ['copy' => true],
        'ThirdRank.blp'         => ['copy' => true],
        'VerdantGlow3.blp'      => ['copy' => true],
    ],

    'directories' => [
        'Legends'               => ['copy' => true],
        'Maps'                  => ['copy' => true],
        'ReplaceableTextures'   => ['copy' => true],
        'Special'               => ['copy' => true],
        'Textures'              => ['copy' => true],
        'UI'                    => ['copy' => true],
        'war3mapImported'       => ['copy' => true],
    ],
];

