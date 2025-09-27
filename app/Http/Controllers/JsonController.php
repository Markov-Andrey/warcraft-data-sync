<?php

namespace App\Http\Controllers;

use App\Services\BlpConverterService;
use App\Services\JsonService;
use App\Services\MapConverterService;
use App\Services\PathService;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    public function units(Request $request)
    {
        $path = PathService::getParentProjectPath();
        $w3u = $path . DIRECTORY_SEPARATOR . 'war3map.w3u';
        $w3uSkin = $path . DIRECTORY_SEPARATOR . 'war3mapSkin.w3u';
        $wts = $path . DIRECTORY_SEPARATOR . 'war3map.wts';
        $w3uJson = $w3u . '.json';
        $w3uSkinJson = $w3uSkin . '.json';
        $wtsJson = $wts . '.json';

        // Конвертация файлов в JSON, если нужно
        if (MapConverterService::shouldConvert($w3u, $w3uJson)) MapConverterService::convertToJson($w3u, $w3u);
        if (MapConverterService::shouldConvert($w3uSkin, $w3uSkinJson)) MapConverterService::convertToJson($w3uSkin, $w3uSkin);
        if (MapConverterService::shouldConvert($wts, $wtsJson)) MapConverterService::convertToJson($wts, $wts);

        // Чтение JSON
        $jsonDataW3u = json_decode(file_get_contents($w3uJson), true);
        $jsonDataW3uSkin = json_decode(file_get_contents($w3uSkinJson), true);
        $jsonDataWts = json_decode(file_get_contents($wtsJson), true);

        $unitsW3u = array_merge($jsonDataW3u['original'] ?? [], $jsonDataW3u['custom'] ?? []);
        $unitsW3uSkin = array_merge($jsonDataW3uSkin['original'] ?? [], $jsonDataW3uSkin['custom'] ?? []);
        $allUnits = array_merge_recursive($unitsW3u, $unitsW3uSkin);

        $tags = JsonService::mapTags();
        $wtsMapping = $jsonDataWts ?? [];
        $idMapping = JsonService::unitsCode();

        $parsedUnits = [];
        $blpService = new BlpConverterService();

        foreach ($allUnits as $unitCode => $paramsList) {
            foreach ($paramsList as $param) {
                // Заменяем TRIGSTR_ на значение из WTS
                if (preg_match('/^TRIGSTR_(\d+)$/', $param['value'], $matches)) {
                    $key = $matches[1];
                    $param['value'] = $wtsMapping[$key] ?? $param['value'];
                }

                // Стандартный параметр
                $paramKey = $idMapping[$param['id']] ?? $param['id'];
                $parsedUnits[$unitCode][$paramKey] = [
                    'name'  => $paramKey,
                    'value' => $param['value'],
                    'type'  => $param['type'],
                    'level' => $param['level'],
                    'column'=> $param['column'],
                ];

                // Если это uico — создаем новое поле uico_png
                if ($param['id'] === 'uico' && !empty($param['value'])) {
                    $blpPath = $path . DIRECTORY_SEPARATOR . $param['value'];
                    try {
                        $pngPath = file_exists($blpPath) ? $blpService->convertToStorage($blpPath, 'png') : null;
                        // Сохраняем относительный путь для Blade
                        $parsedUnits[$unitCode]['uico_png'] = $pngPath
                            ? str_replace(storage_path('app/public') . '/', '', $pngPath)
                            : null;
                    } catch (\Exception $e) {
                        $parsedUnits[$unitCode]['uico_png'] = null;
                    }
                }
            }
        }

        // Фильтруем по легендам, если нужно
        $legends = $request->input('legends');
        if (!empty($legends)) {
            $legendArray = explode(',', $legends);
            $parsedUnits = JsonService::filterUnitsByLegends($parsedUnits, $legendArray);
        }

        // Пересчитываем все ключи после фильтрации
        $allKeys = [];
        foreach ($parsedUnits as $unitParams) {
            foreach ($unitParams as $key => $value) {
                if (!in_array($key, $allKeys)) {
                    $allKeys[] = $key;
                }
            }
        }

        return view('units', [
            'units'   => $parsedUnits,
            'tags'    => $tags,
            'allKeys' => $allKeys,
        ]);
    }
}
