<?php

return [
    'war3map.wts' => [
        '/\[[^\]]*\]\s*/' => '', // зачистка тех тэга [<title>]
    ],
    'war3map.j' => [
        '/set udg_Map="([^"]+)"/' => 'set udg_Map=":project_key"', // смена имени глобальной переменной карты udg_Map на переменную проекта
    ],
];
