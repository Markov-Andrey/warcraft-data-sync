<?php

namespace App\Services;

class JsonService
{
    public static function unitsCode()
    {
        return [
            "uhpm" => "❤️‍(HP)",
            "uhpr" => "❤️(regen)",
            "uhrt" => "❤️‍(type)",
            "ufoo" => "🍖",
            "ugol" => "💰",
            "ulum" => "🌲",
            "umvs" => "🥾",
            "ubld" => "⏳",
            'uabi' => '✨',
            'uhab' => '⚡',
            'urac' => 'Race',
            'unam' => 'Name',
            'uico' => 'Icon',
            'umdl' => 'Model',
        ];
    }

    /**
     * Filter units to legends
     *
     * @param array $units
     * @param array $legendArray
     * @return array
     */
    public static function filterUnitsByLegends(array $units, array $legendArray): array
    {
        $filteredUnits = [];

        foreach ($units as $unitCode => $paramsList) {
            if (!isset($paramsList['unam']['value'])) {
                continue;
            }

            $unitName = strtolower($paramsList['unam']['value']);

            foreach ($legendArray as $legend) {
                if (str_contains($unitName, '[' . strtolower($legend) . ']')) {
                    $filteredUnits[$unitCode] = $paramsList;
                    break;
                }
            }
        }

        return $filteredUnits;
    }

    public static function mapTags()
    {
        return ['Arthas', 'Uther', 'Tyrande', 'Wrynn', 'Whitemane', 'Hellscream', 'Kelthuzad', 'Alterac', 'Silithus'];
    }
}
