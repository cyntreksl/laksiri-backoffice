<?php

namespace Database\Seeders;

use App\Models\NoteType;
use Illuminate\Database\Seeder;

class NoteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['note_type' => 'WOODEN BOX'],
            ['note_type' => 'CARTON'],
            ['note_type' => 'FRIDGE'],
            ['note_type' => 'TV CARTON'],
            ['note_type' => 'COOKER'],
            ['note_type' => 'W/MACHINE'],
            ['note_type' => 'MATT/BED BDL'],
            ['note_type' => 'TRUNK STEEL BOX'],
            ['note_type' => 'TRAVELING BOX'],
            ['note_type' => 'IRON TABLE/LADDER'],
            ['note_type' => 'SOFA SET/BNDL'],
            ['note_type' => 'BNDL'],
            ['note_type' => 'BICYCLE'],
        ];

        if (NoteType::count() === 0) {
            NoteType::insert($data);
        }
    }
}
