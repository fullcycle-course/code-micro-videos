<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Video;

$factory->define(Video::class, function (Faker $faker) {
    $rating = Video::RATING_LIST[array_rand(Video::RATING_LIST)];
    return [
        'title'         => $faker->sentence(3),
        'description'   => $faker->sentence(10),
        'year_launched' => random_int(1895, 2022),
        'rating'        => $rating,
        'opened'        => random_int(0, 1),
        'duration'      => random_int(1, 30),
//        'thumb_file'    => null,
//        'banner_file'   => null,
//        'trailer_file'  => null,
//        'vide_file'     => null,
//        'published'     => random_int(0, 1),
    ];
});
