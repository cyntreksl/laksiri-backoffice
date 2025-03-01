<?php

namespace App\Services;

use App\Models\HBL;

class PriceCalculationService {


    public function __construct()
    {
    }

    public function hblPriceSummary(HBL $hbl): array
    {
//        $hbl = $hbl->load('packages','packages.packageRuleData');
        $priceRuleData = $hbl->packages[0]->packageRuleData;.
        if(!$priceRuleData['is_package_rule']){
            $measuredData = [
                ''
            ]
        }
        dd($priceRules);
    }
}
