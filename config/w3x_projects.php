<?php

return [
    'project_dir' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends",
    'parent_project' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends\\MainProject.w3x",
    'child_projects' => [
        'alterac' => [
            'key' => 'alterac',
            'name' => "Alterac's Justice",
            'info' => [ // w3i
                'map' => [
                    'name' => "Alterac's Justice",
                    'description' => 'Some description for Alterac',
                    'recommendedPlayers' => 'PvE 2vE',
                    'author' => 'AMarkov',
                ],
                'players' => [
                    [
                        'name' => 'P1',
                    ],
                    [
                        'name' => 'P2',
                    ],
                    [
                        'name' => 'Stonebeard',
                    ],
                    [
                        'name' => 'Syndicate Army',
                    ],
                    [
                        'name' => 'Syndicate Caravan',
                    ],
                ],
                'forces' => [
                    [
                        'name' => 'Players',
                    ],
                    [
                        'name' => 'Syndicate',
                    ],
                ],
            ],
            'path' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends\\Child\\AlteracJustice.w3x",
            'individual' => [
                'Maps\\Alterac',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNAbility_Hunter_SniperShot.blp',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNAlterac_drink_11.blp',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNAlterac_food_33.blp',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNBanner_Alterac_Black.blp',
            ],
        ],
        'silithus' => [
            'key' => 'silithus',
            'name' => 'Voices of the Sands',
            'info' => [ // w3i
                'map' => [
                    'name' => 'Voices of the Sands',
                    'description' => 'Some description for Silithus',
                    'recommendedPlayers' => 'PvE 2vE',
                    'author' => 'AMarkov',
                ],
            ],
            'path' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends\\Child\\VoicesOfSands.w3x",
            'individual' => [
                'Maps\\Silithus',
                'World',
            ],
        ],
        'warsong' => [
            'key' => 'warsong',
            'name' => 'Warsong Gulch',
            'info' => [ // w3i
                'map' => [
                    'name' => 'Warsong Gulch',
                    'description' => 'Some description for Warsong',
                    'recommendedPlayers' => 'PvP 2v2',
                    'author' => 'AMarkov',
                ],
            ],
            'path' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends\\Child\\WarsongGulch.w3x",
            'individual' => [
                'Maps\\Warsong',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNwarsong_nightelf_banner.png.blp',
                'ReplaceableTextures\\CommandButtonsDisabled\\DISBTNwarsong_orcs_banner.blp',
            ],
        ],
    ],
    'build_output_path' => "C:\\Users\\Markov\\Documents\\Warcraft III\\Maps\\legends\\builds",
];
