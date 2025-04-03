<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UploadHblImageRequest;
use App\Http\Resources\HblImageResource;
use App\Interfaces\Api\HblImageRepositoryInterface;
use Illuminate\Http\JsonResponse;

class HblImageController extends Controller
{
    public function __construct(
        private readonly HblImageRepositoryInterface $hblImageRepository,
    ) {}

    /**
     * Store HBL Image
     *
     * Store a newly created HBL Image in storage.
     *
     * @group HBL Image
     */
    public function upload(UploadHblImageRequest $request): JsonResponse
    {

        $validatedData = $request->validated();

        $result = $this->hblImageRepository->uploadImages($validatedData);

        if ($result['message'] === 'No images provided.') {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'],
            ], 400);
        }

        return response()->json([
            'message' => 'Images uploaded successfully.',
            'data' => HblImageResource::collection($result['data']),
        ]);
    }
}
