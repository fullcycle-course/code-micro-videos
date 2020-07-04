<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    abstract protected function model();
    abstract protected function rulesStore();
    abstract protected  function rulesUpdate();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();

        return $obj;
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($category)
    {
        return $this->findOrFail($category);
    }

    public function update(Request $request, string $id)
    {
        $obj = $this->findOrFail($id);
        $this->validate($request, $this->rulesUpdate());
        $obj->update($request->all());

        return $obj;
    }

    public function destroy(string $id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();

        return response()->noContent();
    }
}
