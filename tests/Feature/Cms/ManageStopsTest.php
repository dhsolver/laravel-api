<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\TourStop;

class ManageStopTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;
    public $stop;

    public function setUp()
    {
        parent::setUp();

        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);
        $this->stop = create(TourStop::class, ['tour_id' => $this->tour->id])->toArray();
    }

    protected function updateStop($overrides = [])
    {
        $data = array_merge($this->stop->toArray(), $overrides);

        return $this->json('PATCH', route('cms.stop.update', $this->tour->id, $this->stop->id), $data);
    }

    /** @test */
    public function it_can_be_updated_by_its_creator()
    {
        $this->loginAs($this->business);

        $updateStop([
            'title' => 'new title',
            'description' => 'new description',
        ])->assertSee('new title')
            ->assertSee('new description');
    }

    /** @test */
    public function it_cannot_be_updated_by_another_user()
    {
    }

    /** @test */
    public function it_requires_a_title_description_and_type_to_be_updated()
    {
    }
}
