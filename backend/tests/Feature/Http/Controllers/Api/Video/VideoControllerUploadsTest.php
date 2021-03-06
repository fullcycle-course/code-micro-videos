<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'image/jpeg']
        );
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'image/jpeg']
        );
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles(): void
    {
        $category = Category::find($this->sendData['categories_id'][0]);
        $genre = Genre::with(['categories'])->find($this->sendData['genres_id'][0]);
        $categoryResource = new CategoryResource($category);
        $genreResource = new GenreResource($genre);

        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'categories' => [$categoryResource->response()->getData(true)['data']],
                'genres' => [$genreResource->response()->getData(true)['data']],
            ],
        ]);

        foreach($files as $key => $file){
            $expected = \Storage::url("{$response->json('data.id')}/{$file->hashName()}");
            $this->assertEquals($expected, $response->json('data')["{$key}_url"]);
        }

        $id = $response->json('data.id');
        $resource = new VideoResource(Video::find($id));
        $this->assertResource($response, $resource);

        $id = $response->json('data.id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles(): void
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);
        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
        ];
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles
        );

        $response->assertStatus(200);

        $this->assertFilesOnPersist($response,
            Arr::except($files, ['thumb_file', 'video_file']) + $newFiles
        );
        $id = $response->json('data.id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
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

    private function assertFilesOnPersist(\Illuminate\Foundation\Testing\TestResponse $response, array $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }
}
