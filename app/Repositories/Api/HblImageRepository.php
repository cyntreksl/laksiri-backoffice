<?php

namespace App\Repositories\Api;

use App\Interfaces\Api\HblImageRepositoryInterface;
use App\Models\HBLImage;
use Illuminate\Support\Facades\DB;

class HblImageRepository implements HblImageRepositoryInterface
{
    public function uploadImages(array $data)
    {
        if (empty($data['images']) || ! is_array($data['images'])) {
            return [
                'message' => 'No images provided.',
                'data' => [],
            ];
        }

        $uploadedImages = [];

        DB::transaction(function () use ($data, &$uploadedImages) {
            foreach ($data['images'] as $image) {
                $path = $image->store('uploads', 'public');

                $imageData = [
                    'image_path' => $path,
                    'image_type' => $data['image_type'],
                    'hbl_id' => $data['hbl_id'] ?? null,
                    'hbl_packages_id' => $data['hbl_packages_id'] ?? null,
                    'officer_id' => $data['officer_id'] ?? null,
                ];

                $uploadedImages[] = HblImage::create($imageData);
            }
        });

        return [
            'message' => 'Images uploaded successfully.',
            'data' => $uploadedImages,
        ];

    }
}
