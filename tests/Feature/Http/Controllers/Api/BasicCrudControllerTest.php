<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Stubs\Controllers\CategoryStubController;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    private $controller;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryStubController();
        $this->request = \Mockery::mock(Request::class);
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $result   = $this->controller->index()->toArray($this->request);
        $this->assertEquals([$category->toArray()], $result);
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        $this->request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);
        $obj = $this->controller->store($this->request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray($this->request)
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $category         = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $reflactionClass  = new \ReflectionClass(BasicCrudController::class);
        $reflactionMethod = $reflactionClass->getMethod('findOrFail');
        $reflactionMethod->setAccessible(true);

        $result = $reflactionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflactionClass  = new \ReflectionClass(BasicCrudController::class);
        $reflactionMethod = $reflactionClass->getMethod('findOrFail');
        $reflactionMethod->setAccessible(true);

        $result = $reflactionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testUpdate()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

        $this->request->shouldReceive('all')
            ->twice()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);
        $obj = $this->controller->update($this->request, $category->id);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray($this->request)
        );
    }

    public function testDelete()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $obj = $this->controller->destroy($category->id);
        $this->assertInstanceOf(Response::class, $obj);
    }

}
