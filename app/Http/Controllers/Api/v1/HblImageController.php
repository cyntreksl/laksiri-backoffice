<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UploadHblImageRequest;
use App\Http\Resources\HblImageResource;
use App\Interfaces\Api\HblImageRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class HblImageController extends Controller
{
    use ResponseAPI;

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
            return $this->error($result['message'], $result['data'], 400);
        }

        return $this->success(
            'Images uploaded successfully.',
            HblImageResource::collection($result['data'])
        );
    }
}
