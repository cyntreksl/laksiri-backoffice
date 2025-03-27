<?php

namespace App\Interfaces\Api;

use Illuminate\Http\Request;


interface HblImageRepositoryInterface
{
    public function uploadImages(array $data);

}
