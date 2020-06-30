<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre    = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre    = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response->assertStatus(200)->assertJson($genre->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequired($response);
        $this->assertMissingValidationErrors($response);

        $response = $this->json('POST', route('genres.store'), [
            'name'      => str_repeat('a', 256),
            'is_active' => 'g',
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $genre    = factory(Genre::class)->create();
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);
        $this->assertInvalidationRequired($response);
        $this->assertMissingValidationErrors($response);


        $genre    = factory(Genre::class)->create();
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            [
                'name'      => str_repeat('a', 256),
                'is_active' => 'g',
            ]
        );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    /**
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     */
    private function assertInvalidationRequired(TestResponse $response): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name']),
            ]);
    }

    /**
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     */
    private function assertMissingValidationErrors(TestResponse $response): void
    {
        $response->assertStatus(422)
            ->assertJsonMissingValidationErrors(['is_active']);
    }

    /**
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     */
    private function assertInvalidationMax(TestResponse $response): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            ]);
    }

    /**
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     */
    private function assertInvalidationBoolean(TestResponse $response): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active']),
            ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
        ]);
        $id       = $response->json('id');
        $genre    = Genre::find($id);

        $response->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name'      => 'test',
            'is_active' => false,
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
        ]);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name'      => 'test',
            'is_active' => false,
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name'      => 'test_updated',
            'is_active' => true,
        ]);
        $id       = $response->json('id');
        $genre    = Genre::find($id);

        $response->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name'      => 'test_updated',
                'is_active' => true,
            ]);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));
        $response->assertSuccessful();
        $genre->refresh();
        $this->assertNotNull($genre->deleted_at);
    }
}
