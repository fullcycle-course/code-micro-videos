<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResource;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResource;

    private $castMember;
    private $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR,
        ]);
    }

    public function testIndex()
    {
        $response = $this->get(route('cast-members.index'));

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);
        $resourceClass = $this->resource();
        $resource = $resourceClass::collection(collect([$this->castMember]));
        $this->assertResource($response, $resource);

    }

    public function testShow()
    {
        $response = $this->get(route('cast-members.show', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $id = $response->json('data.id');
        $resourceClass = $this->resource();
        $resource = new $resourceClass(CastMember::find($id));
        $this->assertResource($response, $resource);
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
        $data = [
            'type' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            [
                'name' => 'test',
                'type' => CastMember::TYPE_DIRECTOR,
            ],
            [
                'name' => 'test_2',
                'type' => CastMember::TYPE_ACTOR,
            ],
        ];

        foreach ($data as $key => $value) {
            $response  = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $id = $response->json('data.id');
            $resourceClass = $this->resource();
            $resource = new $resourceClass(CastMember::find($id));
            $this->assertResource($response, $resource);
        }
    }

    public function testUpdate()
    {
        $this->castMember = factory(CastMember::class)->create([
            'name' => 'test_1',
            'type' => CastMember::TYPE_ACTOR,
        ]);

        $data = [
            'name' => 'test_update',
            'type' => CastMember::TYPE_DIRECTOR,
        ];
        $response = $this->assertUpdate($data, $data);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        $id = $response->json('data.id');
        $resourceClass = $this->resource();
        $resource = new $resourceClass(CastMember::find($id));
        $this->assertResource($response, $resource);
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('cast-members.destroy', ['cast_member' => $this->castMember->id]));
        $response->assertSuccessful();

        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
    }

    protected function routeStore()
    {
        return route('cast-members.store');
    }

    protected function routeUpdate()
    {
        return route('cast-members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }
}
