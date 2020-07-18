<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create([
            'name'      => 'test',
            'is_active' => true,
        ]);
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response->assertStatus(200)->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response->assertStatus(200)->assertJson($this->genre->toArray());
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

    public function testInvalidationCategoriesIdField(): void
    {
        $data = [
            'categories_id' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = [
            'categories_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testSave()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        $data     = [
            'send_data' => [
                'name'          => 'test',
                'is_active'     => false,
                'categories_id' => [$category->id],
            ],
            'test_data' => [
                'name'      => 'test',
                'is_active' => false,
            ],
        ];
        $this->assertStore($data['send_data'], $data['test_data']);
        $response = $this->assertUpdate($data['send_data'], $data['test_data']);
        $response->assertJsonFragment([
            'is_active' => false,
        ]);
    }

    public function testDelete(): void
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));
        $response->assertSuccessful();

        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }
}
