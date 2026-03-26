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

    public static function updateValue($db, $id, $key, $value, $type = 'string')
    {
        try {
            $dbFile = PathService::getParentProjectPath() . '/' . $db;

            $jsonContent = file_get_contents($dbFile);
            $data = json_decode($jsonContent, true);
            if (!$data) {
                throw new \Exception("Failed to decode JSON data.");
            }

            $unitKeyFound = null;
            foreach ($data['custom'] as $unitKey => $unitData) {
                if (strpos($unitKey, $id . ":") === 0) {
                    $unitKeyFound = $unitKey;
                    break;
                }
            }

            if (!$unitKeyFound) {
                throw new \Exception("Unit with id '$id' not found in the data.");
            }

            $unitData = &$data['custom'][$unitKeyFound];
            $foundField = false;

            foreach ($unitData as &$field) {
                if ($field['id'] === $key) {
                    $field['value'] = self::castValue($value, $field['type']);
                    $foundField = true;
                    break;
                }
            }

            if (!$foundField) {
                $unitData[] = [
                    'id' => $key,
                    'type' => $type,
                    'level' => 0,
                    'column' => 0,
                    'value' => self::castValue($value, $type),
                ];
            }

            file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
