<?php

namespace App\Interfaces;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function storeHBL(array $data);
}
