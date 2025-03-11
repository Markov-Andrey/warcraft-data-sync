<?php

namespace App\Services;

class CrudJson
{
    public static function updateValue($db, $id, $key, $value)
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
                    $field['value'] = $value;
                    $foundField = true;
                    break;
                }
            }

            if (!$foundField) {
                throw new \Exception("Field with key '$key' not found.");
            }

            file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
