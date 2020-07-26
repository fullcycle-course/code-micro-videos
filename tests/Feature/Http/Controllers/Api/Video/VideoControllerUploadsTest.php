<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestValidations;
use Tests\Traits\TestUploads;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads;

    public function testInvalidationVideoField(): void
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            50000000,
            'mimetypes', ['values' => 'video/mp4']
        );
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            5000,
            'mimetypes', ['values' => 'image/jpeg']
        );
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            10000,
            'mimetypes', ['values' => 'image/jpeg']
        );
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            1000000,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles(): void
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id],
            ] +
            $files
        );
        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    protected function getFiles(): array
    {
        return [
            'video_file'   => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file'   => UploadedFile::fake()->create('thumb_file.jpg'),
            'banner_file'  => UploadedFile::fake()->create('banner_file.jpg'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4'),
        ];
    }

    protected function routeStore(): string
    {
        return route('videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model(): string
    {
        return Video::class;
    }
}
