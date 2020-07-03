<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;

class CategoryStubController extends BasicCrudController
{
    protected function model()
    {
        return CategoryStub::class;
    }

    protected function rulesStore()
    {
        return [
            'name'      => 'required|max:255',
            'description' => 'nullable'
        ];
    }



}
