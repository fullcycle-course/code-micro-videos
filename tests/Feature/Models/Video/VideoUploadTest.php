<?php

namespace Feature\Models\Video;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;
use Tests\Feature\Models\Video\BaseVideoTestCase;

class VideoUploadTest extends BaseVideoTestCase
{
    protected $fileFieldsData;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (Video::$fileFields as $field) {
            $this->fileFieldsData[$field] = "$field.test";
        }
    }

    public function testUpdateWithBasicFields(): void
    {
        /** @var Video $genre */
        $video = factory(Video::class)->create([
            'opened' => false,
        ]);
        $video->update($this->data + $this->fileFieldsData);
        foreach ($this->data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }

        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = factory(Video::class)->create([
            'opened' => false,
        ]);
        $video->update($this->data + $this->fileFieldsData + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $this->fileFieldsData + ['opened' => true]);
    }

    public function testUpdateWithRelations(): void
    {
        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();
        $video    = factory(Video::class)->create();
        $video->update($this->data + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id],
            ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testUpdateWithFiles(): void
    {
        \Storage::fake();
        $video       = factory(Video::class)->create();
        $thumbFile   = UploadedFile::fake()->image('thumb.jpg');
        $videoFile   = UploadedFile::fake()->create('video.mp4');
        $bannerFile  = UploadedFile::fake()->create('banner.jpg');
        $trailerFile = UploadedFile::fake()->create('video.mp4');
        $video->update($this->data + [
                'thumb_file'   => $thumbFile,
                'video_file'   => $videoFile,
                'banner_file'  => $bannerFile,
                'trailer_file' => $trailerFile,
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->image('video.mp4');
        $newBannerFile = UploadedFile::fake()->image('banner.jpg');
        $video->update($this->data + [
                'video_file' => $newVideoFile,
                'banner_file' => $newBannerFile,
            ]
        );
        \Storage::assertExists("{$video->id}/{$thumbFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newBannerFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$videoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$bannerFile->hashName()}");
    }

    public function testUpdateIfRollbackFiles(): void
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;

        try {
            $video->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4'),
                ]
            );
        }
        catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testFileUrlWithLocalDriver(): void
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }

        $video = factory(Video::class)->create($fileFields);
        $localDriver = config('filesystems.default');
        $baseUrl = config('filesystems.disks.' . $localDriver)['url'];
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlWithGcsDriver(): void
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }

        $video = factory(Video::class)->create($fileFields);
        $baseUrl = config('filesystems.disks.gcs.storage_api_uri');
        \Config::set('filesystems.default', 'gcs');
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlsIfNullWhenFieldsAreNull(): void
    {
        $video = factory(Video::class)->create();
        foreach (Video::$fileFields as $field) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertNull($fileUrl);
        }
    }
}
