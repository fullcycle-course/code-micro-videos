<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = \DB::transaction(function() use($request, $validatedData, $self) {
            /** @var Genre $obj */
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return new GenreResource($obj);
    }

    public function update(Request $request, string $id)
    {
        $obj = $this->findOrFail($id);
        $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj = \DB::transaction(function() use($request, $obj, $self){
            $obj->update($request->all());
            $self->handleRelations($obj, $request);

            return $obj;
        });
        return new GenreResource($obj);;
    }

    protected function handleRelations($genre, Request $request): void
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model(): string
    {
        return Genre::class;
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
        return GenreResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

}
