<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface GridJsInterface
{
    public function dataset(int $limit =10,int $offset=0,string $order='id',string $direction='asc',string $search=null,array $filters = []);

    public function getSummery(array $filters = []) ;
}
