<?php

namespace App\Services;

class JsonService
{
    public static function unitsCode(): array
    {
        return [
            "udaa" => "Abilities - Default Active Ability",
            "uhas" => "Abilities - Hero Skin", // garbage
            "uabi" => "Abilities - Normal",
            "uabs" => "Abilities - Normal Skin", // garbage
            "utcc" => "Art - Allow Custom Team Color", // garbage
            "uble" => "Art - Animation - Blend Time (seconds)", // garbage
            "ucbs" => "Art - Animation - Cast Backswing", // garbage
            "ucpt" => "Art - Animation - Cast Point", // garbage
            "urun" => "Art - Animation - Run Speed", // garbage
            "uwal" => "Art - Animation - Walk Speed", // garbage
            "ubpx" => "Art - Button Position (X)",
            "ubpy" => "Art - Button Position (Y)",
            "ucua" => "Art - Caster Upgrade Art",
            "udtm" => "Art - Death Time (seconds)",
            "uept" => "Art - Elevation - Sample Points", // garbage
            "uerd" => "Art - Elevation - Sample Radius", // garbage
            "ufrd" => "Art - Fog of War - Sample Radius", // garbage
            "ushr" => "Art - Has Water Shadow", // garbage
            'uico' => 'Art - Icon - Game Interface',
            'ussi' => 'Art - Icon - Score Screen', // hero
            'umxp' => 'Art - Maximum Pith Angel (degrees)', // garbage
            'umxr' => 'Art - Maximum Roll Angel (degrees)', // garbage
            'umdl' => 'Art - Model File',
            'uver' => 'Art - Model File - Extra Version', // garbage
            'uocc' => 'Art - Occluder Height', // garbage
            'uori' => 'Art - Orientation Interpolation', // garbage
            'upor' => 'Art - Portrait Model File',
            'uimz' => 'Art - Projectile Impact - Z',
            'uisz' => 'Art - Projectile Impact - Z (Swimming)',
            'ulpx' => 'Art - Projectile Launch - X',
            'ulpy' => 'Art - Projectile Launch - Y',
            'ulpz' => 'Art - Projectile Launch - Z',
            'uprw' => 'Art - Propulsion Window (degrees)',
            'uani' => 'Art - Required Animation Names',
            'uaap' => 'Art - Required Animation Names - Attachment',
            'ualp' => 'Art - Required Animation Link Names',
            'ubpr' => 'Art - Required Bone Names',
            'uscb' => 'Art - Scale Projectile',
            'usca' => 'Art - Scaling Value',
            'uslz' => 'Art - Selection Circle - Height', // garbage
            'usew' => 'Art - Selection Circle on Water', // garbage
            'ussc' => 'Art - Selection Scale',
            'ushu' => 'Art - Shadow Image (Unit)',
            'ushx' => 'Art - Shadow Image - Center X', // garbage
            'ushy' => 'Art - Shadow Image - Center Y', // garbage
            'ushh' => 'Art - Shadow Image - Height', // garbage
            'ushw' => 'Art - Shadow Image - Width', // garbage
            'ushb' => 'Art - Shadow Texture - Building',
            'uspa' => 'Art - Special',
            'utaa' => 'Art - Art',
            'utco' => 'Team Color', // garbage
            'uclr' => 'Tinting Color 1 (Red)',
            'uclg' => 'Tinting Color 2 (Green)',
            'uclb' => 'Tinting Color 3 (Blue)',
            'ulos' => 'Use Extended Line of Sight', // garbage
            "uhpm" => "❤️‍(HP)",
            "uhpr" => "❤️(regen)",
            "uhrt" => "❤️‍(type)",
            "ufoo" => "🍖",
            "ugol" => "💰",
            "ulum" => "🌲",
            "umvs" => "🥾",
            "ubld" => "⏳",
            'uhab' => '⚡',
            'urac' => 'Race',
            'unam' => 'Name',
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
