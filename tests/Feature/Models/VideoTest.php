<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid as RUuid;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 120,
        ];;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
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
            'created_at',
            'updated_at',
            'deleted_at',
        ], $videosKeys);
    }

    public function testCreateWithBasicFields(): void
    {
        $video = Video::create($this->data);
        $video->refresh();

        foreach ($this->data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
        $this->assertNull($video->delete_at);
        $this->assertFalse($video->opened);
        $this->assertTrue(RUuid::isValid($video->id));
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', ['opened' => true]);
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

    public function testUpdateWithBasicFields(): void
    {
        /** @var Video $genre */
        $video = factory(Video::class)->create([
            'opened' => false,
        ]);
        $video->update($this->data);
        foreach ($this->data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }

        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = factory(Video::class)->create([
            'opened' => false,
        ]);
        $video->update($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testUpdateWithRelations(): void
    {
        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();
        $video = factory(Video::class)->create();
        $video->update($this->data + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id],
            ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id]
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

    public function testRollbackUpdate()
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

    protected function assertHasCategory($videoId, $categoryId): void
    {
        $this->assertDatabaseHas('category_video', [
            'video_id'    => $videoId,
            'category_id' => $categoryId,
        ]);
    }

    protected function assertHasGenre($videoId, $genreId): void
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId,
        ]);
    }

}
