<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid as RUuid;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();

        $this->assertCount(1, $genres);
        $genresKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'name',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $genresKeys);
    }

    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'genreTest',
        ]);
        $genre->refresh();

        $this->assertNotNull($genre->name);
        $this->assertEquals('genreTest', $genre->name);
        $this->assertNull($genre->delete_at);
        $this->assertTrue($genre->is_active);
        $this->assertTrue(RUuid::isValid($genre->id));

        $genre = Genre::create([
            'name' => 'genreTest',
            'is_active' => false,
        ]);
        $this->assertFalse($genre->is_active);
    }

    public function testUpdate()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create()->first();

        $data = [
            'name' => 'genre_updated',
            'is_active' => false
        ];

        $genre->update($data);
        foreach($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create()->first();
        $genre->delete();
        $genreDeleted = Genre::onlyTrashed()->get()->first();

        $this->assertEquals($genre->id, $genreDeleted->id);
        $this->assertNotNull($genreDeleted->deleted_at);
    }
}
