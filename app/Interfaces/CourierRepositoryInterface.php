<?php

namespace App\Interfaces;

use App\Models\Courier;

interface CourierRepositoryInterface
{
    public function storeCourier(array $data);

    public function deleteCourier(Courier $courier);

    public function changeStatus(array $courierData, string $status);

    public function updateCourier(Courier $courier, array $data);

    public function downloadCourier(Courier $courier);

    public function downloadCourierInvoice(Courier $courier);

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []);
}
