<?php

namespace App\Http\Controllers;

use App\Services\JsonService;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    public function units()
    {
        $jsonPath = storage_path('app/projects/Alterac.w3x/war3map.w3u.json');
        $idMapping = JsonService::unitsCode();
        $jsonData = json_decode(file_get_contents($jsonPath), true);
        $units = array_merge($jsonData['original'] ?? [], $jsonData['custom'] ?? []);

        $parsedUnits = [];
        foreach ($units as $unitCode => $paramsList) {
            foreach ($paramsList as $param) {
                $uniqueKey = $unitCode . '_' . $param['id'] . '_' . $param['level'];
                $parsedUnits[$unitCode][$uniqueKey] = [
                    'name' => $idMapping[$param['id']] ?? $param['id'],
                    'value' => $param['value'],
                    'type'  => $param['type'],
                    'level' => $param['level'],
                    'column'=> $param['column'],
                ];
            }
        }

        return view('units', ['units' => $parsedUnits]);
    }
}
