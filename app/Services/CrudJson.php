<?php

namespace App\Services;

class CrudJson
{
    // TODO есть проблема, нужно создавать поле когда его нет в json и важен тип "int" - число, и "unreal" - float, а значение храни без кавычек если это реально числа. вот пример - {"id":"ua1s","type":"int","level":0,"column":0,"value":21},{"id":"ua1r","type":"int","level":0,"column":0,"value":1150}. а структура json {"original":{"uplg":[{"id":"uabi","type":"string","level":0,"column":0,"value":"Aap2,A01A"}]},"custom":{"U006:Uear":[{"id":"udup","type":"int","level":0,"column":0,"value":1},{"id":"uhab","type":"string","level":0,"column":0,"value":"A018,A021,A01B,A0A8"},{"id":"ubld","type":"int",
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
