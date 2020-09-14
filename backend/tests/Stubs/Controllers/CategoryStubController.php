<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Resources\CategoryStubResource;

class CategoryStubController extends BasicCrudController
{
    protected function model()
    {
        return CategoryStub::class;
    }

    protected function resource()
    {
        return CategoryStubResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function rulesStore()
    {
        return [
            'name'      => 'required|max:255',
            'description' => 'nullable'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name'      => 'required|max:255',
            'description' => 'nullable'
        ];
    }



}
