<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Models\DamagedGarment;
use App\Models\DamageEvidence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidencesController extends Controller
{
    use ApiResponses;

    /**
     * POST /api/v1/evidences
     * multipart/form-data: image (file) + damaged_garment_id (int)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'damaged_garment_id' => 'required|integer|exists:damaged_garments,id',
            'image'              => 'required|file|image|max:10240', // 10 MB
        ]);

        DamagedGarment::findOrFail($request->damaged_garment_id);

        $file      = $request->file('image');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $path      = 'evidences/' . Str::uuid() . '.' . $extension;
        $disk      = 'public';

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $evidence = DamageEvidence::create([
            'damaged_garment_id' => $request->damaged_garment_id,
            'path'               => $path,
            'disk'               => $disk,
            'size_bytes'         => $file->getSize(),
        ]);

        return $this->successResponse([
            'id'       => $evidence->id,
            'url'      => $evidence->url,
            'path'     => $evidence->path,
            'size_bytes' => $evidence->size_bytes,
        ], '', 201);
    }

    /**
     * GET /api/v1/evidences/{id}
     */
    public function show(int $id): JsonResponse
    {
        $evidence = DamageEvidence::findOrFail($id);

        return $this->successResponse([
            'id'         => $evidence->id,
            'url'        => $evidence->url,
            'path'       => $evidence->path,
            'size_bytes' => $evidence->size_bytes,
        ]);
    }

    /**
     * DELETE /api/v1/evidences/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $evidence = DamageEvidence::findOrFail($id);

        Storage::disk($evidence->disk)->delete($evidence->path);
        $evidence->delete();

        return response()->json(['message' => 'Evidencia eliminada.']);
    }
}
