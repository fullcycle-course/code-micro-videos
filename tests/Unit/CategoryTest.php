<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    public function testFillableAttribute()
    {
        $category = new Category();
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $category->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits         = [
            SoftDeletes::class,
            Uuid::class,
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testDatesAttribute()
    {
        $dates    = ['created_at', 'updated_at', 'deleted_at'];
        $category = new Category();
        foreach ($dates as $date){
            $this->assertContains($date, $category->getDates());
        }
        $this->assertCount(count($dates), $category->getDates());
    }

    public function testCastsAttribute()
    {
        $category = new Category();
        $casts    = ['id' => 'string'];
        $this->assertEquals($casts, $category->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $category = new Category();
        $this->assertFalse($category->incrementing);
    }
}
