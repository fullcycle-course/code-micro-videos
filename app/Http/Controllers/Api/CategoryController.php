<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BasicCrudController
{
    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return [
            'name'        => 'required|max:255',
            'description' => 'nullable',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function show($category)
    {
        return $this->findOrFail($category);
    }

    public function update(Request $request, string $categoryId)
    {
        $category = $this->findOrFail($categoryId);
        $this->validate($request, $this->rulesStore());
        $category->update($request->all());

        return $category;
    }

    public function destroy(string $categoryId)
    {
        $category = $this->findOrFail($categoryId);
        $category->delete();

        return response()->noContent();
    }


}
