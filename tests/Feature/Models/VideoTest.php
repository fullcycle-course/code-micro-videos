<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid as RUuid;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

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

    public function testCreate(): void
    {
        $data  = [
            'title'         => 'videoTest',
            'description'   => 'description',
            'year_launched' => 2019,
            'rating'        => 'L',
            'duration'      => 120,
        ];
        $video = Video::create($data);
        $video->refresh();

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
        $this->assertNull($video->delete_at);
        $this->assertFalse($video->opened);
        $this->assertTrue(RUuid::isValid($video->id));

        $video = Video::create($data + ['opened' => true]);
        $this->assertTrue($video->opened);
    }

    public function testUpdate(): void
    {
        /** @var Video $genre */
        $genre = factory(Video::class)->create([
            'opened' => false,
        ]);

        $data = [
            'title'  => 'video_updated',
            'opened' => true,
        ];

        $genre->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
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
}
