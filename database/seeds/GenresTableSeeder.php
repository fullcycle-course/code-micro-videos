<?php

use Illuminate\Database\Seeder;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = \App\Models\Category::all();
        factory(\App\Models\Genre::class, 50)
            ->create()
            ->each(function(Genre $genre) use($categories){
                $categoriesId = $categories->random(5)->pluck('id')->toArray();
                $genre->categories()->attatch($categoriesId);
            });
    }
}
