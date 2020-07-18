<?php

namespace App\Http\Controllers\Api;

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

        ];
    }

    public function store(Request $request)
    {
        $this->addRuleInfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesStore());
        $self          = $this;
        $obj           = \DB::transaction(function () use ($request, $validatedData, $self) {
            /** @var Video $obj */
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);

            return $obj;
        });
        $obj->refresh();

        return $obj;
    }

    public function update(Request $request, string $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleInfGenreHasCategories($request);
        $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj  = \DB::transaction(function () use ($request, $obj, $self) {
            $obj->update($request->all());
            $self->handleRelations($obj, $request);

            return $obj;
        });

        return $obj;
    }

    protected function addRuleInfGenreHasCategories(Request $request)
    {
        $categoriesId               = is_array($request->get('categories_id')) ? $request->get('categories_id') : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule(
            $categoriesId
        );
    }

    protected function handleRelations($video, Request $request): void
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
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

}
