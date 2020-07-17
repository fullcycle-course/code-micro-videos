<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video    = factory(Video::class)->create([
            'opened' => false,
        ]);
        $this->sendData = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90,
        ];
    }

    public function testIndex(): void
    {
        $response = $this->get(route('videos.index'));

        $response->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow(): void
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response->assertStatus(200)->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired(): void
    {
        $data = [
            'title'         => '',
            'description'   => '',
            'year_launched' => '',
            'rating'        => '',
            'duration'      => '',
            'categories_id' => '',
            'genres_id'     => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax(): void
    {
        $data = [
            'title' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger(): void
    {
        $data = [
            'duration' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField(): void
    {
        $data   = [
            'year_launched' => 'a',
        ];
        $format = ['format' => 'Y'];
        $this->assertInvalidationInStoreAction($data, 'date_format', $format);
        $this->assertInvalidationInUpdateAction($data, 'date_format', $format);
    }

    public function testInvalidationOpenedField(): void
    {
        $data = [
            'opened' => 's',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField(): void
    {
        $data = [
            'rating' => 0,
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesIdField(): void
    {
        $data = [
            'categories_id' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = [
            'categories_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationGenresIdField(): void
    {
        $data = [
            'genres_id' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = [
            'genres_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testSave(): void
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        /** @var Genre $genre */
        $genre    = factory(Genre::class)->create();
        $data     = [
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genres_id'     => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['opened' => false, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genres_id'     => [$genre->id],
                        'opened'        => true,
                    ],
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genres_id'     => [$genre->id],
                        'rating'        => Video::RATING_LIST[2],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[2]],
            ],
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);
            $response->assertJsonStructure([
                'created_at',
                'updated_at',
            ]);
            $response = $this->assertUpdate($value['send_data'], $value['test_data']);
            $response->assertJsonStructure([
                'created_at',
                'updated_at',
            ]);
        }
    }

    public function testRollbackStore(): void
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        try {
            $controller->store($request);
        }
        catch (TestException $e) {
            $this->assertCount(1, Video::all());
        }
    }


    public function testDelete(): void
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertSuccessful();

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
