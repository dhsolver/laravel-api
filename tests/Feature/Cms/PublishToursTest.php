<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use Illuminate\Support\Carbon;
use App\TourType;
use Tests\HasTestTour;

class PublishToursTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken, HasTestTour;

    public $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = createUser('client');

        [$this->tour, $this->stops] = $this->createTestAdventure(true, $this->client);
        $this->tour->update(['published_at' => null]);

        // $this->tour = create('App\Tour', ['user_id' => $this->client->id]);

        // $stop = $this->tour->stops()->create(factory('App\TourStop')->make()->toArray());
        // $media = factory('App\Media')->create(['user_id' => $this->client->id]);

        // $this->tour->update(['main_image_id' => $media->id]);
        // $stop->update(['main_image_id' => $media->id]);

        // $this->tour->location()->delete();
        // $stop->location()->delete();

        // factory('App\Location')->create([
        //     'locationable_type' => 'App\Tour',
        //     'locationable_id' => $this->tour->id,
        // ]);

        // factory('App\Location')->create([
        //     'locationable_type' => 'App\TourStop',
        //     'locationable_id' => $this->tour->stops()->first()->id,
        // ]);
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

        $this->tour->update(['user_id' => $admin->id]);
        $this->loginAs($admin);

        $this->assertCount(0, $this->tour->publishSubmissions);
        $this->assertFalse($this->tour->isAwaitingApproval);
        $this->assertFalse($this->tour->isPublished);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
        $this->assertTrue($this->tour->fresh()->isPublished);
        $this->assertCount(1, $this->tour->fresh()->publishSubmissions);
    }

    /** @test */
    public function a_tour_must_have_all_required_data_to_be_published()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $this->tour->update(['description' => null]);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour->fresh()->id]))
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['errors', 'tour']])
            ->assertSee('The tour has no description');
    }

    /** @test */
    public function an_admin_can_approve_a_pending_tour()
    {
        $this->tour->submitForPublishing();
        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);

        $this->signIn('admin');

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
        $this->assertTrue($this->tour->fresh()->isPublished);
    }

    /** @test */
    public function a_tour_must_have_an_in_app_id_to_approve_publishing()
    {
        $this->tour->submitForPublishing();
        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);

        $this->signIn('admin');

        $this->tour->update(['in_app_id' => '']);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(422)
            ->assertSee('In-App ID');

        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);

        $this->tour->update(['in_app_id' => 'test']);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
        $this->assertTrue($this->tour->fresh()->isPublished);
    }

    /** @test */
    public function a_tour_must_have_routes_in_order_to_be_publish()
    {
        $this->loginAs($this->client);

        $this->tour->update(['type' => TourType::OUTDOOR]);
        $this->tour->syncRoute([]);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(422);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);
    }

    /** @test */
    public function an_adveture_must_have_all_the_stop_routes_to_be_published()
    {
        $this->loginAs($this->client);

        [$this->tour, $this->stops] = $this->createTestAdventure(false, $this->client);
        $this->tour->update(['published_at' => null]);

        $media = factory('App\Media')->create(['user_id' => $this->client->id]);
        $this->tour->update(['main_image_id' => $media->id]);
        $this->tour->location()->delete();
        factory('App\Location')->create([
            'locationable_type' => 'App\Tour',
            'locationable_id' => $this->tour->id,
        ]);

        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(422);

        $this->assertFalse($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);

        $this->insertStopRouteData($this->tour->fresh());
        $this->putJson(route('cms.tours.publish', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertTrue($this->tour->fresh()->isAwaitingApproval);
        $this->assertFalse($this->tour->fresh()->isPublished);
    }
}
