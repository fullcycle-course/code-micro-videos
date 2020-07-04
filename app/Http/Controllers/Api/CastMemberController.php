<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;

class CastMemberController extends BasicCrudController
{
    protected function model()
    {
        return CastMember::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'integer',
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
