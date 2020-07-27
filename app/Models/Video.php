<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const RATING_LIST = ['L', '10', '14', '16', '18'];

    public const THUMB_FILE_MAX_SIZE   = 1024 * 5; // 5MB
    public const BANNER_FILE_MAX_SIZE  = 1024 * 10; // 10MB
    public const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1; // 1GB
    public const VIDEO_FILE_MAX_SIZE   = 1024 * 1024 * 50; // 50GB


    protected $fillable = [
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
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id'            => 'string',
        'opened'        => 'boolean',
        'year_launched' => 'integer',
        'duration'      => 'integer',
    ];

    public        $incrementing = false;
    public static $fileFields   = ['video_file', 'thumb_file', 'banner_file', 'trailer_file'];


    public function getVideoFileUrlAttribute()
    {
        return $this->getFileUrl($this->video_file);
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->getFileUrl($this->thumb_file);
    }

    public function getBannerFileUrlAttribute()
    {
        return $this->getFileUrl($this->banner_file);
    }

    public function getTrailerFileUrlAttribute()
    {
        return $this->getFileUrl($this->trailer_file);
    }

    public static function create(array $attributes): ?Video
    {
        $files = self::extractFiles($attributes);

        try {
            \DB::beginTransaction();
            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);

            $obj->uploadFiles($files);
            \DB::commit();

            return $obj;
        }
        catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = []): ?Video
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $isSaved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($isSaved) {
                $this->uploadFiles($files);
            }
            \DB::commit();
            if ($isSaved && count($files)) {
                $this->deleteOldFiles();
            }

            return $isSaved;
        }
        catch (\Exception $e) {
            $this->deleteFiles($files);
            \DB::rollBack();
            throw $e;
        }
    }

    public static function handleRelations(Video $video, array $attributes): void
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }


}
