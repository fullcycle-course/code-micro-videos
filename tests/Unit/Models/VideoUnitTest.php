<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Video;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

use Tests\TestCase;

class VideoUnitTest extends TestCase
{
    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    public function testFillableAttribute(): void
    {
        $fillable = ['title', 'description', 'year_launched', 'opened','rating', 'duration', 'video_file', 'thumb_file', 'banner_file', 'trailer_file'];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testIfUseTraits(): void
    {
        $traits         = [
            SoftDeletes::class,
            Uuid::class,
            UploadFiles::class,
        ];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testDatesAttribute(): void
    {
        $dates    = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($dates as $date){
            $this->assertContains($date, $this->video->getDates());
        }
        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testCastsAttribute(): void
    {
        $casts = [
            'id'            => 'string',
            'opened'        => 'boolean',
            'year_launched' => 'integer',
            'duration'      => 'integer',
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testIncrementingAttribute(): void
    {
        $this->assertFalse($this->video->incrementing);
    }

    public function testRelationships(): void
    {
        $this->assertTrue(method_exists($this->video, 'categories'));
        $this->assertTrue(method_exists($this->video, 'genres'));
    }

    public function testConstRatingList(): void
    {
        $this->assertIsArray(Video::RATING_LIST);
        $this->assertEquals(['L', '10', '14', '16', '18'], Video::RATING_LIST);
    }
}
