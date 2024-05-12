<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashSettlementCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id'=>$this->id??'-',
          'hbl'=>$this->reference??'-',
          'hbl_name'=>$this->hbl_name??'-',
          'address'=>$this->address??'-',
          'picked_date'=>$this->picked_date??'1',
          'weight'=>$this->weight??'2',
          'volume'=>$this->volume??'3',
          'grand_total'=>$this->grand_total??'-',
          'paid_amount'=>$this->paid_amount??'-',
          'cargo_type'=>$this->cargo_type??'-',
          'hbl_type'=>$this->hbl_type??'-',
          'officer'=>$this->created_by??'-',
          'actions'=>'-',
        ];
    }
}
