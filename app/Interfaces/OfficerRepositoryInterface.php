<?php

namespace App\Interfaces;

interface OfficerRepositoryInterface
{
    public function getShippers();

    public function getConsignees();

    public function getAllofficers();
}
