<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Обновить статус 'copy' для файла/директории
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCopyStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $path = $request->input('path');
        $copy = $request->input('copy');
        $updated = $this->configService->updateCopyStatus($path, (bool) $copy);
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function updateCopyChild(Request $request)
    {
        $path = $request->input('path');
        $child = $request->input('child');
        $updated = $this->configService->updateCopyChild($path, (string) $child);
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function updateValidateStatus()
    {
        $updated = $this->configService->updateValidateStatus();
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
    public function copyChild()
    {
        $updated = $this->configService->copyChildFiles();
        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Path not found in config'], 404);
        }
    }
}
