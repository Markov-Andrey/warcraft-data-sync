<?php

return [
    'war3map.wts' => [
        '/^[\s]*\[[^\]]*\]\s*/' => '', // зачистка тех тэга [<title>]
        '/:project_name/' => ':project_name', // имя карты
        '/:description/' => ':description', // описание карты
        '/:type_game/' => ':type_game', // кол-во игроков
        '/:author/' => ':author', // автор
    ],
    'war3map.j' => [
        '/set udg_Map="([^"]+)"/' => 'set udg_Map=":project_key"', // смена имени глобальной переменной карты udg_Map на переменную проекта
    ],
];
