<?php

namespace App\Services;

class CrudJson
{
    private static function castValue($value, string $type)
    {
        if ($type === 'int') return (int) $value;
        if ($type === 'unreal' || $type === 'real') return (float) $value;
        return (string) $value;
    }

    private static function detectType($value): string
    {
        if (is_numeric($value) && strpos((string) $value, '.') !== false) return 'unreal';
        if (is_numeric($value) && strpos((string) $value, '.') === false) return 'int';
        return 'string';
    }

    public static function updateValue($db, $id, $key, $value, $type = 'string')
    {
        try {
            $dbFile = PathService::getParentProjectPath() . '/' . $db;

            $jsonContent = file_get_contents($dbFile);
            $data = json_decode($jsonContent, true);
            if (!$data) {
                throw new \Exception("Failed to decode JSON data.");
            }

            // Search in custom (keys like "U006:Uear")
            $unitKeyFound = null;
            foreach ($data['custom'] as $unitKey => $unitData) {
                if (strpos($unitKey, $id . ":") === 0) {
                    $unitKeyFound = $unitKey;
                    break;
                }
            }

            if ($unitKeyFound !== null) {
                $section = 'custom';
                $sectionKey = $unitKeyFound;
            } elseif (isset($data['original'][$id])) {
                $section = 'original';
                $sectionKey = $id;
            } else {
                throw new \Exception("Unit with id '$id' not found in the data.");
            }

            $unitData = &$data[$section][$sectionKey];
            $foundField = false;

            foreach ($unitData as &$field) {
                if ($field['id'] === $key) {
                    $field['value'] = self::castValue($value, $field['type']);
                    $foundField = true;
                    break;
                }
            }

            if (!$foundField) {
                $detectedType = self::detectType($value);
                $unitData[] = [
                    'id' => $key,
                    'type' => $detectedType,
                    'level' => 0,
                    'column' => 0,
                    'value' => self::castValue($value, $detectedType),
                ];
            }

            file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
