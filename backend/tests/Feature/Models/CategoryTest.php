<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Ramsey\Uuid\Uuid as RUuid;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $categoryKeys);
    }

    public function testCreate()
    {
        $category = Category::create([
            'name' => 'test1',
        ]);
        $category->refresh();

        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name'        => 'test1',
            'description' => null,
        ]);
        $this->assertNull($category->description);

        $category = Category::create([
            'name'      => 'test1',
            'is_active' => false,
        ]);

        $this->assertFalse($category->is_active);

        $category = Category::create([
            'name'        => 'test1',
            'description' => 'test_description',
        ]);
        $this->assertEquals('test_description', $category->description);

        $category = Category::create([
            'name'      => 'test1',
            'is_active' => true,
        ]);

        $this->assertTrue($category->is_active);


        $category = Category::create([
            'name' => 'test1',
        ]);
        $this->assertIsString($category->id);
        $this->assertTrue(RUuid::isValid($category->id));
    }

    public function testUpdate()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active'=> false,
        ])->first();

        $data = [
            'name'        => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active'   => true,
        ];

        $category->update($data);

        foreach($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create()->first();
        $category->delete();
        $categoryDeleted = Category::onlyTrashed()->get()->first();

        $this->assertEquals($category->id, $categoryDeleted->id);
        $this->assertNotNull($categoryDeleted->deleted_at);
    }

}
