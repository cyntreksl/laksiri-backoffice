<?php

namespace App\Repositories\Api;

use App\Interfaces\Api\HblImageRepositoryInterface;
use App\Models\HBLImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        //        try {
        DB::transaction(function () use ($data, &$uploadedImages) {
            foreach ($data['images'] as $image) {
                $path = $image->store('uploads', 'public');

                $imageData = [
                    'image_path' => $path,
                    'image_type' => $data['image_type'],
                    'hbl_id' => $data['hbl_id'] ?? null,
                    'hbl_packages_id' => $data['hbl_packages_id'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                ];

                // Handle shipper_nic or shipper_passport separately
                if ($data['image_type'] === 'shipper_nic') {
                    $imageData['shipper_nic'] = $data['shipper_nic'] ?? $path;
                } elseif ($data['image_type'] === 'shipper_passport') {
                    $imageData['shipper_passport'] = $data['shipper_passport'] ?? $path;
                } elseif ($data['image_type'] === 'package') {
                    $imageData['package_images'] = json_encode($data['package_images'] ?? []);
                }

                $uploadedImages[] = HblImage::create($imageData);
            }
        });

        return [
            'message' => 'Images uploaded successfully.',
            'data' => $uploadedImages,
        ];

        //        } catch (\Exception $e) {
        //            Log::error('Image Upload Error: ' . $e->getMessage());
        //            return [
        //                'message' => 'Failed to upload images.',
        //                'error' => $e->getMessage(),
        //                'data' => []
        //            ];
        //        }
    }

    //    public function uploadImages(array $data): array
    //    {
    //        if (!isset($data['images']) || empty($data['images'])) {
    //            return [
    //                'message' => 'No images provided.',
    //                'data' => []
    //            ];
    //        }
    //
    //        $uploadedImages = [];
    //        foreach ($data['images'] as $image) {
    //            // Process each image (e.g., save to storage, generate URL, etc.)
    //            $path = $image->store('images', 'public'); // Example storage path
    //            $uploadedImages[] = ['path' => $path];
    //        }
    //
    //        return [
    //            'message' => 'Images processed successfully.',
    //            'data' => $uploadedImages
    //        ];
    //    }
}
