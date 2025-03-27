<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HblImageResource;
use App\Interfaces\Api\HblImageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


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
    public function upload(Request $request): JsonResponse
    {

        $validatedData = $request->all();


        $result = $this->hblImageRepository->uploadImages($validatedData);

        if ($result['message'] === 'No images provided.') {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data']
            ], 400);
        }



        return response()->json([
            'message' => 'Images uploaded successfully.',
            'data' => HblImageResource::collection($result['data'])
        ]);
    }

}
