<?php

namespace App\Interfaces;

interface OfficerRepositoryInterface
{
    public function getShippers();

    public function getConsignees();

    public function getAllofficers();

    public function storeshipperOfficers(array $data);

    public function updateShipper(array $data, $id);

    public function destroyShippers($id);
}
