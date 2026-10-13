<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\EventImportService;
use App\Services\Api\PersonnelImportService;
use App\Services\Api\PersonnelSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Token-protected import/export webservices (docs/admin/api.md), successors
 * of the legacy eBrigade api/ scripts. Thin: the services hold the
 * validation and persistence, ApiException renders every refusal.
 */
class ImportExportController extends Controller
{
    public function searchPersonnel(Request $request, PersonnelSearchService $service): JsonResponse
    {
        return response()->json($service->search($request->json()->all()));
    }

    public function importPersonnel(Request $request, PersonnelImportService $service): JsonResponse
    {
        return $this->success($service->import($request->json()->all()));
    }

    public function importEvent(Request $request, EventImportService $service): JsonResponse
    {
        return $this->success($service->import($request->json()->all()));
    }

    /** @param array{id:int,created:bool,message:string} $result */
    private function success(array $result): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'errnum' => 0,
            'message' => $result['message'],
            'id' => $result['id'],
        ], $result['created'] ? 201 : 200);
    }
}
