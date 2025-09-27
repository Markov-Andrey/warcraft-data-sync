<?php

namespace App\Services;

class JsonService
{
    public static function unitsCode(): array
    {
        return [
            "udaa" => "Abilities - Default Active Ability",
            "uabi" => "Abilities - Normal",
            "uabs" => "Abilities - Normal Skin", // garbage
            "uhab" => "Abilities - Hero",
            "uhas" => "Abilities - Hero Skin", // garbage
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
            'utaa' => 'Art - Target',
            'utco' => 'Art - Team Color', // garbage
            'uclr' => 'Art - Tinting Color 1 (Red)',
            'uclg' => 'Art - Tinting Color 2 (Green)',
            "uclb" => "Art - Tinting Color 3 (Blue)",
            "ulos" => "Art - Use Extended Line of Sight",

            "ucaq" => "Combat - Acquisition Range",
            "uam"  => "Combat - Armor Type",

            "ubs1" => "Combat - Attack 1 - Animation Backswing Point",
            "udp1" => "Combat - Attack 1 - Animation Damage Point",
            "ua1f" => "Combat - Attack 1 - Area of Effect (Full Damage)",
            "ua1h" => "Combat - Attack 1 - Area of Effect (Medium Damage)",
            "ua1q" => "Combat - Attack 1 - Area of Effect (Small Damage)",
            "ua1p" => "Combat - Attack 1 - Area of Effect Targets",
            "ua1t" => "Combat - Attack 1 - Attack Type",
            "ua1c" => "Combat - Attack 1 - Cooldown Time",
            "ua1b" => "Combat - Attack 1 - Damage Base",
            "uhd1" => "Combat - Attack 1 - Damage Factor - Medium",
            "ugd1" => "Combat - Attack 1 - Damage Factor - Small",
            "ua1l" => "Combat - Attack 1 - Damage Loss Factor",
            "ua1d" => "Combat - Attack 1 - Damage Number of Dice",
            "ua1s" => "Combat - Attack 1 - Damage Sides per Die",
            "usd1" => "Combat - Attack 1 - Damage Spill Distance",
            "usr1" => "Combat - Attack 1 - Damage Spill Radius",
            "udl1" => "Combat - Attack 1 - Damage Upgrade Amount",
            "uct1" => "Combat - Attack 1 - Maximum Number of Targets",
            "uma1" => "Combat - Attack 1 - Projectile Arc",
            'ua1m' => 'Combat - Attack 1 - Projectile Art',
            "umh1" => "Combat - Attack 1 - Projectile Homing Enabled",
            "ua1z" => "Combat - Attack 1 - Projectile Speed",
            "ua1r" => "Combat - Attack 1 - Range",
            "urb1" => "Combat - Attack 1 - Range Motion Buffer",
            "uwu1" => "Combat - Attack 1 - Show UI",
            "ua1g" => "Combat - Attack 1 - Targets Allowed",
            "ucs1" => "Combat - Attack 1 - Weapon Sound",
            "ua1w" => "Combat - Attack 1 - Weapon Type",

            "ubs2" => "Combat - Attack 2 - Animation Backswing Point",
            "udp2" => "Combat - Attack 2 - Animation Damage Point",
            "ua2f" => "Combat - Attack 2 - Area of Effect (Full Damage)",
            "ua2h" => "Combat - Attack 2 - Area of Effect (Medium Damage)",
            "ua2q" => "Combat - Attack 2 - Area of Effect (Small Damage)",
            "ua2p" => "Combat - Attack 2 - Area of Effect Targets",
            "ua2t" => "Combat - Attack 2 - Attack Type",
            "ua2c" => "Combat - Attack 2 - Cooldown Time",
            "ua2b" => "Combat - Attack 2 - Damage Base",
            "uhd2" => "Combat - Attack 1 - Damage Factor - Medium",
            "uqd2" => "Combat - Attack 2 - Damage Factor - Small",
            "udl2" => "Combat - Attack 2 - Damage Loss Factor",
            "ua2d" => "Combat - Attack 2 - Damage Number of Dice",
            "ua2s" => "Combat - Attack 2 - Damage Sides per Die",
            "usd2" => "Combat - Attack 2 - Damage Spill Distance",
            "usr2" => "Combat - Attack 2 - Damage Spill Radius",
            "udu2" => "Combat - Attack 2 - Damage Upgrade Amount",
            "utc2" => "Combat - Attack 2 - Maximum Number of Targets",
            "uma2" => "Combat - Attack 1 - Projectile Arc",
            'ua2m' => 'Combat - Attack 1 - Projectile Art',
            "umh2" => "Combat - Attack 1 - Projectile Homing Enabled",
            "ua2z" => "Combat - Attack 1 - Projectile Speed",
            "ua2r" => "Combat - Attack 2 - Range",
            "urb2" => "Combat - Attack 2 - Range Motion Buffer",
            "uwu2" => "Combat - Attack 2 - Show UI",
            "ua2g" => "Combat - Attack 2 - Targets Allowed",
            "ucs2" => "Combat - Attack 2 - Weapon Sound",
            "ua2w" => "Combat - Attack 2 - Weapon Type",

            "usem" => "Combat - Attacks Enabled",
            "udea" => "Combat - Death Type",
            "udef" => "Combat - Defense Base",
            "udy" => "Combat - Defense Type",
            "udup" => "Combat - Defense Upgrade Bonus",
            "uamm" => "Combat - Minimum Attack Range",
            "utar" => "Combat - Targeted as",

            "udro" => "Editor - Can Drop Items On Death",
            "ucam" => "Editor - Categorization - Campaign",
            "uspe" => "Editor - Categorization - Special",
            "uhos" => "Editor - Display as Neutral Hostile",
            "utss" => "Editor - Has Tleset Specific Data",
            "uine" => "Editor - Placeable in Editor",
            "util" => "Editor - Tlesets",
            "uuch" => "Editor - Use Click Helper",

            "urpo" => "Movement - Group Separation - Enabled",
            "urpg" => "Movement - Group Separation - Group Number",
            "urpp" => "Movement - Group Separation - Parameter",
            "urpr" => "Movement - Group Separation - Priority",
            "umvh" => "Movement - Height",
            "umvf" => "Movement - Height Minimum",
            "umvs" => "Movement - Speed Base",
            "umas" => "Movement - Speed Maximum",
            "umis" => "Movement - Speed Minimum",
            "umvr" => "Movement - Turn Rate",
            "umvl" => "Movement - Type",

            "uabr" => "Pathing - AI Placement Radius",
            "uabt" => "Pathing - AI Placement Type",
            "ucol" => "Pathing - Collision Size",

            "ulfi" => "Sound - Looping Fade In Rate",
            "ulfo" => "Sound - Looping Fade Out Rate",
            "umsl" => "Sound - Movement",
            "ursl" => "Sound - Random",
            "usnd" => "Sound - Unit Sound Set",

            "ubld" => "Stats - Build Time",
            "ufle" => "Stats - Can Flee",
            "ufoo" => "Stats - Food Cost",
            "ufma" => "Stats - Food Produced",
            "ufor" => "Stats - Formation Rank",
            "ubba" => "Stats - Gold Bounty Awarded - Base",
            "ubdi" => "Stats - Gold Bounty Awarded - Number of Dice",
            "ubsi" => "Stats - Gold Bounty Awarded - Sides per Die",
            "ugol" => "Stats - Gold Cost",
            "uhom" => "Stats - Hide Minimap Display",
            "ufpm" => "Stats - Hit Points Maximum (Base)",
            "ufpr" => "Stats - Hit Points Regeneration Rate",
            "uhrt" => "Stats - Hit Points Regeneration Type",
            "ubdg" => "Stats - Is a Building",
            "ulev" => "Stats - Level",
            "ulba" => "Stats - Lumber Bounty Awarded - Base",
            "ulbd" => "Stats - Lumber Bounty Awarded - Number of Dice",
            "ulbs" => "Stats - Lumber Bounty Awarded - Sides per Die",
            "ulum" => "Stats - Lumber Cost",
            "umpi" => "Stats - Mana Initial Amount",
            "umpm" => "Stats - Mana Maximum",
            "umpr" => "Stats - Mana Regeneration",
            "upoi" => "Stats - Point Value",
            "upri" => "Stats - Priority",
            "urac" => "Stats - Race",
            "ugor" => "Stats - Repair Gold Cost",
            "ulur" => "Stats - Repair Lumber Cost",
            "urtm" => "Stats - Repair Time",
            "usid" => "Stats - Sight Radius (Day)",
            "usin" => "Stats - Sight Radius (Night)",
            "usle" => "Stats - Sleeps",
            "usti" => "Stats - Stock Initial After Start Delay",
            "usma" => "Stats - Stock Maximum",
            "usrg" => "Stats - Stock Replenish Interval",
            "usst" => "Stats - Stock Start Delay",
            "ucar" => "Stats - Transported Size",
            "utyp" => "Stats - Unit Classification",

            "udep" => "Techtree - Dependency Equivalents",
            "uset" => "Techtree - Items Sold",
            "ureq" => "Techtree - Requirements",
            "urqa" => "Techtree - Requirements - Levels",
            "ubui" => "Techtree - Structures Built",
            "useu" => "Techtree - Units Sold",
            "upgr" => "Techtree - Upgrades Used",

            "ucun" => "Text - Caster Upgrade Names",
            "ucut" => "Text - Caster Upgrade Tips",
            "ides" => "Text - Description",
            "uhot" => "Text - Hotkey",
            "unam" => "Text - Name",
            "unsf" => "Text - Name - Editor Suffix",
            "utip" => "Text - Tooltip - Basic",
            "uawt" => "Text - Tooltip - Extended",
            "utpr" => "Text - Tooltip - Awaken", // hero
            "utub" => "Text - Tooltip - Revive", // hero
            "upro" => "Text - Proper Names", // hero
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
            if (!isset($paramsList['Text - Name']['value'])) {
                continue;
            }

            $unitName = strtolower($paramsList['Text - Name']['value']);

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
