<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Ramsey\Uuid\Uuid as RUuid;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMember = CastMember::all();
        $this->assertCount(1, $castMember);
        $castMemberKeys = array_keys($castMember->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'name',
            'type',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $castMemberKeys);
    }

    public function testCreate()
    {
        $castMember = CastMember::create([
            'name' => 'test1',
            'type' => 1,
        ]);
        $castMember->refresh();

        $this->assertEquals('test1', $castMember->name);
        $this->assertEquals(1, $castMember->type);
        $this->assertTrue(RUuid::isValid($castMember->id));
    }

    public function testUpdate()
    {
        /** @var CastMember $castMember */
        $castMember = factory(CastMember::class)->create([
            'name' => 'test',
            'type' => 1,
        ])->first();

        $data = [
            'name' => 'test_name_updated',
            'type' => 2,
        ];

        $castMember->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete()
    {
        /** @var CastMember $castMember */
        $castMember = factory(CastMember::class)->create()->first();
        $castMember->delete();
        $castMemberDeleted = CastMember::onlyTrashed()->get()->first();

        $this->assertEquals($castMember->id, $castMemberDeleted->id);
        $this->assertNotNull($castMemberDeleted->deleted_at);
    }

}
