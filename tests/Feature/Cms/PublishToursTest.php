<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use Illuminate\Support\Carbon;

class PublishToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = createUser('client');

        $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("cms.tours.$name", $this->tour->id);
    }

    /** @test */
    public function a_user_can_submit_a_tour_to_be_published()
    {
        $this->loginAs($this->client);

        $this->assertCount(0, $this->tour->publishSubmissions);
        $this->assertFalse($this->tour->isAwaitingApproval);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);
    }

    /** @test */
    public function a_user_cannot_set_a_tour_as_published()
    {
        $data = array_merge($this->tour->toArray(), ['published_at' => Carbon::now()->toDateTimeString()]);

        $this->json('PATCH', route('cms.tours.update', $this->tour->id), $data);

        $this->assertNull($this->tour->fresh()->published_at);
    }

    /** @test */
    public function a_user_can_revoke_their_publish_submission()
    {
        $this->loginAs($this->client);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);

        $this->putJson(route('cms.tours.unpublish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
    }

    /** @test */
    public function a_user_can_unpublish_their_tour()
    {
        $this->withoutExceptionHandling();
        $this->loginAs($this->client);

        $this->tour->published_at = \Carbon\Carbon::now();
        $this->tour->save();
        $this->assertTrue($this->tour->fresh()->isPublished);

        $this->putJson(route('cms.tours.unpublish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertFalse($this->tour->fresh()->isPublished);
    }

    /** @test */
    public function admin_owned_tours_auto_approve_when_submitted_for_publishing()
    {
        $admin = createUser('admin');

        $tour = create('App\Tour', ['user_id' => $admin->id]);
        $this->loginAs($admin);

        $this->assertCount(0, $tour->publishSubmissions);
        $this->assertFalse($tour->isAwaitingApproval);
        $this->assertFalse($tour->isPublished);

        $this->putJson(route('cms.tours.publish', ['tour' => $tour]))
            ->assertStatus(200);

        $this->assertFalse($tour->fresh()->isAwaitingApproval);
        $this->assertTrue($tour->fresh()->isPublished);
        $this->assertCount(1, $tour->fresh()->publishSubmissions);
    }
}
