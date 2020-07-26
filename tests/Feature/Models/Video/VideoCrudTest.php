<?php


namespace Tests\Feature\Models\Video;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid as RUuid;
use Tests\Exceptions\TestException;

class VideoCrudTest extends BaseVideoTestCase
{
    protected $fileFieldsData;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (Video::$fileFields as $field) {
            $this->fileFieldsData[$field] = "$field.test";
        }
    }

    public function testList(): void
    {
        factory(Video::class)->create();
        $videos = Video::all();

        $this->assertCount(1, $videos);
        $videosKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'video_file',
            'thumb_file',
            'banner_file',
            'trailer_file',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $videosKeys);
    }

    public function testCreateWithBasicFields(): void
    {
        $video = Video::create($this->data + $this->fileFieldsData);
        $video->refresh();

        foreach ($this->data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
        $this->assertNull($video->delete_at);
        $this->assertFalse($video->opened);
        $this->assertTrue(RUuid::isValid($video->id));
        $this->assertDatabaseHas('videos', $this->data + $this->fileFieldsData + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', ['opened' => true]);
    }

    public function testCreateWithFiles(): void
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file'   => UploadedFile::fake()->image('thumb.jpg'),
                'video_file'   => UploadedFile::fake()->image('video.mp4'),
                'banner_file'  => UploadedFile::fake()->image('banner.jpg'),
                'trailer_file' => UploadedFile::fake()->image('video.mp4'),
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
    }

    public function testCreateIfRollbackFiles(): void
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;

        try {
            $video = Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4'),
                    'banner_file'  => UploadedFile::fake()->image('banner.jpg'),
                    'trailer_file' => UploadedFile::fake()->image('video.mp4'),
                ]
            );
        }
        catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testCreateWithRelations(): void
    {
        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();

        $video = Video::create($this->data + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id],
            ]);
        $video->refresh();

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }


    public function testHandleRelations(): void
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id],
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id],
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);
    }

    public function testDelete(): void
    {
        /** @var Video $video */
        $video = factory(Video::class)->create();
        $video->delete();
        $videoDeleted = Video::onlyTrashed()->get()->first();

        $this->assertEquals($video->id, $videoDeleted->id);
        $this->assertNotNull($videoDeleted->deleted_at);
    }

    public function testRollbackCreate(): void
    {
        $hasError = false;
        try {
            Video::create([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);

        }
        catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate(): void
    {
        $video    = factory(Video::class)->create();
        $oldTitle = $video->title;
        try {
            $video->update([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);

        }
        catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle,
            ]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }
}
