<?php

namespace App\Http\Controllers;

use App\Services\JsonService;
use App\Services\MapConverterService;
use App\Services\PathService;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    public function units(Request $request)
    {
        $path = PathService::getProjectPath();
        $w3u = $path . '\war3map.w3u';
        $w3uSkin = $path . '\war3mapSkin.w3u';
        $wts = $path . '\war3map.wts';
        $w3uJson = $w3u . '.json';
        $w3uSkinJson = $w3uSkin . '.json';
        $wtsJson = $wts . '.json';
        if (MapConverterService::shouldConvert($w3u, $w3uJson)) {
            MapConverterService::convertToJson($w3u, $w3uJson);
        }
        if (MapConverterService::shouldConvert($w3uSkin, $w3uSkinJson)) {
            MapConverterService::convertToJson($w3uSkin, $w3uSkinJson);
        }
        if (MapConverterService::shouldConvert($wts, $wtsJson)) {
            MapConverterService::convertToJson($wts, $wtsJson);
        }

        $jsonDataW3u = json_decode(file_get_contents($w3uJson), true);
        $jsonDataW3uSkin = json_decode(file_get_contents($w3uSkinJson), true);
        $jsonDataWts = json_decode(file_get_contents($wtsJson), true);

        $unitsW3u = array_merge($jsonDataW3u['original'] ?? [], $jsonDataW3u['custom'] ?? []);
        $unitsW3uSkin = array_merge($jsonDataW3uSkin['original'] ?? [], $jsonDataW3uSkin['custom'] ?? []);
        $allUnits = array_merge_recursive($unitsW3u, $unitsW3uSkin);

        $tags = JsonService::mapTags();

        $wtsMapping = $jsonDataWts ?? [];

        $parsedUnits = [];
        $idMapping = JsonService::unitsCode();

        foreach ($allUnits as $unitCode => $paramsList) {
            foreach ($paramsList as $param) {
                // Если значение содержит паттерн TRIGSTR_, извлекаем замену из WTS
                if (preg_match('/^TRIGSTR_(\d+)$/', $param['value'], $matches)) {
                    $key = $matches[1];
                    $param['value'] = $wtsMapping[$key] ?? $param['value'];  // Заменяем на значение из WTS, если найдено
                }

                // Уникальный ключ для каждого параметра
                $parsedUnits[$unitCode][$param['id']] = [
                    'name'  => $idMapping[$param['id']] ?? $param['id'],
                    'value' => $param['value'],
                    'type'  => $param['type'],
                    'level' => $param['level'],
                    'column'=> $param['column'],
                ];
            }
        }

        $legends = $request->input('legends');
        if (!empty($legends)) {
            $legendArray = explode(',', $legends);
            $parsedUnits = JsonService::filterUnitsByLegends($parsedUnits, $legendArray);
        }

        return view('units', ['units' => $parsedUnits, 'tags' => $tags]);
    }
}
