<?php


namespace Tests\Feature\Http\Controllers\Api\Video;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResource;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResource;

    protected $video;
    protected $sendData;
    protected $serializedFields = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file_url',
        'thumb_file_url',
        'banner_file_url',
        'trailer_file_url',
        'categories',
        'genres',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false,
        ]);
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $this->sendData = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90,
            'categories_id' => [$category->id],
            'genres_id'     => [$genre->id],
        ];
    }

}
