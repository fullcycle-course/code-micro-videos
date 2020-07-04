<?php

namespace Tests\Unit\Models;

use App\Models\Traits\Uuid;
use \App\Models\CastMember;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }


    public function testFillableAttribute()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits         = [
            SoftDeletes::class,
            Uuid::class,
        ];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $castMemberTraits);
    }

    public function testDatesAttribute()
    {
        $dates    = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($dates as $date){
            $this->assertContains($date, $this->castMember->getDates());
        }
        $this->assertCount(count($dates), $this->castMember->getDates());
    }

    public function testCastsAttribute()
    {
        $casts    = ['id' => 'string'];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->castMember->incrementing);
    }
}
