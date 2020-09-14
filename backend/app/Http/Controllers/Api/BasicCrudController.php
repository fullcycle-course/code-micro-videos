<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
    protected $paginationSize = 15;
    abstract protected function model();
    abstract protected function rulesStore();
    abstract protected  function rulesUpdate();
    abstract protected function resource();
    abstract protected function resourceCollection();

    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollectionClass);
        return $refClass->isSubClassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($category)
    {
        $obj =  $this->findOrFail($category);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, string $id)
    {
        $obj = $this->findOrFail($id);
        $this->validate($request, $this->rulesUpdate());
        $obj->update($request->all());
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function destroy(string $id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();

        return response()->noContent();
    }
}
