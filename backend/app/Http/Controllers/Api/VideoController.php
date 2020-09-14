<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title'         => 'required|max:255',
            'description'   => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened'        => 'boolean',
            'rating'        => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration'      => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id'     => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
            'video_file'    => 'nullable|file|mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
            'thumb_file'    => 'nullable|file|mimetypes:image/jpeg|max:' . Video::THUMB_FILE_MAX_SIZE,
            'banner_file'   => 'nullable|file|mimetypes:image/jpeg|max:' . Video::BANNER_FILE_MAX_SIZE,
            'trailer_file'  => 'nullable|file|mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE,
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleInfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesStore());
        /** @var Video $obj */
        $obj = $this->model()::create($validatedData);
        $obj->refresh();

        return new VideoResource($obj);
    }

    public function update(Request $request, string $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleInfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validatedData);

        return new VideoResource($obj);
    }

    protected function addRuleInfGenreHasCategories(Request $request)
    {
        $categoriesId               = is_array($request->get('categories_id')) ? $request->get('categories_id') : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule(
            $categoriesId
        );
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    protected function resource()
    {
        return VideoResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

}
