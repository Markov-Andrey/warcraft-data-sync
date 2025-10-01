<?php

namespace App\Http\Controllers;

use App\Services\BuildGameService;
use App\Services\ConfigService;
use App\Services\FileProcessorService;
use App\Services\InfoConfigService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }
    public function copyChild()
    {
        $updated = FileProcessorService::copyChildFiles();
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function setBuild()
    {
        $updated = (new BuildGameService)->buildAll();
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function switchProject(Request $request)
    {
        $select = $request->input('select');
        $updated = FileProcessorService::switch($select);
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function setVersion(Request $request)
    {
        $version = $request->input('version');
        InfoConfigService::selectedVersion($version);

        return response()->json(['success' => true]);
    }
}
