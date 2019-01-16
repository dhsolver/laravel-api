<?php

namespace Tests\Feature\Cms;

use App\TourType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\Media;

class ManageToursTest extends TestCase
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
    public function a_tour_requires_a_title_and_proper_types_to_be_updated()
    {
        $this->loginAs($this->client);

        $this->updateTour(['title' => null])->assertStatus(422);
        $this->updateTour(['pricing_type' => null])->assertStatus(422);
        $this->updateTour(['type' => null])->assertStatus(422);
    }

    /** @test */
    public function a_tour_can_be_updated_by_its_creator()
    {
        $this->loginAs($this->client);

        $data = [
            'title' => 'test title',
            'description' => 'test desc',
            'pricing_type' => Tour::$PRICING_TYPES[0],
            'type' => \App\TourType::all()[0],
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function a_tour_cannot_be_updated_by_another_user()
    {
        $this->signIn('client');

        $this->updateTour([
            'title' => 'new title',
            ])
            ->assertStatus(403);
    }

    protected function updateTour($overrides = [])
    {
        $data = array_merge($this->tour->toArray(), $overrides);

        return $this->json('PATCH', route('cms.tours.update', $this->tour->id), $data);
    }

    /** @test */
    public function a_user_can_get_a_list_of_only_their_tours()
    {
        $this->withExceptionHandling();

        $otherTour = create('App\Tour', ['title' => md5('unique title string')]);

        $this->assertCount(2, Tour::all());

        $this->loginAs($this->client);

        $this->json('GET', route('cms.tours.index'))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->tour->title])
            ->assertJsonMissing(['title' => $otherTour->title]);
    }

    /** @test */
    public function a_tour_can_be_deleted_by_its_creator()
    {
        $this->loginAs($this->client);

        $this->assertCount(1, $this->client->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(200);

        $this->assertCount(0, $this->client->fresh()->tours);
    }

    /** @test */
    public function a_tour_cannot_be_deleted_by_another_user()
    {
        $this->signIn('client');

        $this->assertCount(1, $this->client->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(403);

        $this->assertCount(1, $this->client->fresh()->tours);
    }

    /** @test */
    public function a_tour_can_be_seen_by_its_creator()
    {
        $this->loginAs($this->client);

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->tour->title]);
    }

    /** @test */
    public function a_tour_cannot_be_seen_by_another_user()
    {
        $this->signIn('client');

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_update_a_tours_address()
    {
        $this->loginAs($this->client);

        $data = [
            'location' => [
                'address1' => md5('123 Elm St.'),
                'address2' => md5('APT 805'),
                'city' => md5('New York'),
                'country' => 'US',
                'state' => 'NY',
                'zipcode' => '10001',
                'latitude' => 40.12343657,
                'longitude' => -74.0242935,
            ],
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertJsonFragment($data['location']);
    }

    /** @test */
    public function a_user_can_update_the_tours_social_url()
    {
        $this->loginAs($this->client);

        $fb = 'https://facebook.com/test';
        $ig = 'https://instagram.com/test';
        $tw = 'https://twitter.com/test';

        $this->updateTour([
            'facebook_url' => $fb,
            'twitter_url' => $tw,
            'instagram_url' => $ig,
        ])->assertStatus(200);

        $t = $this->tour->fresh();
        $this->assertEquals($t->facebook_url, $fb);
        $this->assertEquals($t->twitter_url, $tw);
        $this->assertEquals($t->instagram_url, $ig);
    }

    /** @test */
    public function social_urls_must_be_valid()
    {
        $this->loginAs($this->client);

        $bad_url = 'test';
        $this->updateTour([
            'facebook_url' => $bad_url,
            'twitter_url' => $bad_url,
            'instagram_url' => $bad_url,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['facebook_url', 'twitter_url', 'instagram_url']);

        $bad_url = 'https://google.com/test';
        $this->updateTour([
            'facebook_url' => $bad_url,
            'twitter_url' => $bad_url,
            'instagram_url' => $bad_url,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['facebook_url', 'twitter_url', 'instagram_url']);
    }

    /** @test */
    public function a_tours_video_urls_require_a_valid_youtube_urls()
    {
        $this->loginAs($this->client);

        $url = 'https://www.youtube.com/watch?v=abcd1234';

        $data = [
            'video_url' => $url,
            'start_video_url' => $url,
            'end_video_url' => $url
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $url = 'https://www.google.com/';
        $data = [
            'video_url' => $url,
            'start_video_url' => $url,
            'end_video_url' => $url
        ];

        $this->updateTour($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_url', 'start_video_url', 'end_video_url']);

        $url = 'not a url';
        $data = [
            'video_url' => $url,
            'start_video_url' => $url,
            'end_video_url' => $url
        ];

        $this->updateTour($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_url', 'start_video_url', 'end_video_url']);
    }

    /** @test */
    public function a_tour_can_have_a_prize()
    {
        $this->loginAs($this->client);

        $updates = [
            'prize_details' => 'details',
            'prize_instructions' => 'instructions',
            'has_prize' => true,
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJsonFragment($updates);
    }

    /** @test */
    public function a_tour_prize_can_have_a_time_limit()
    {
        $this->loginAs($this->client);

        $updates = [
            'prize_details' => 'details',
            'prize_instructions' => 'instructions',
            'prize_time_limit' => 24,
            'has_prize' => true,
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJsonFragment($updates);
    }

    /** @test */
    public function a_tour_can_have_a_start_point()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $stop = create('App\TourStop', ['tour_id' => $this->tour]);

        $updates = [
            'start_point_id' => '' . $stop->id,
            'start_message' => 'starting message',
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJsonFragment($updates);
    }

    /** @test */
    public function a_start_point_must_be_a_stop_on_the_tour()
    {
        $this->loginAs($this->client);

        $otherTour = create('App\Tour', ['user_id' => $this->client->id]);
        $stop = create('App\TourStop', ['tour_id' => $otherTour]);

        $this->updateTour(['start_point_id' => $stop->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_point_id']);
    }

    /** @test */
    public function a_tour_can_have_an_endpoint()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $stop = create('App\TourStop', ['tour_id' => $this->tour]);

        $updates = [
            'end_point_id' => '' . $stop->id,
            'end_message' => 'end message',
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJsonFragment($updates);
    }

    /** @test */
    public function an_end_point_must_be_a_stop_on_the_tour()
    {
        $this->loginAs($this->client);

        $otherTour = create('App\Tour', ['user_id' => $this->client->id]);
        $stop = create('App\TourStop', ['tour_id' => $otherTour]);

        $this->updateTour(['end_point_id' => $stop->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('end_point_id');
    }

    /** @test */
    public function tour_media_can_be_updated()
    {
        $this->loginAs($this->client);

        $media = Media::create([
            'file' => 'images/test.jpg',
            'user_id' => $this->client->id,
            'type' => Media::TYPE_IMAGE,
        ]);

        $data = [
            'main_image_id' => '' . $media->id,
            'start_image_id' => '' . $media->id,
            'end_image_id' => '' . $media->id,
            'trophy_image_id' => '' . $media->id,
            'image1_id' => '' . $media->id,
            'image2_id' => '' . $media->id,
            'image3_id' => '' . $media->id,
            'intro_audio_id' => '' . $media->id,
            'background_audio_id' => '' . $media->id,
            'pin_image_id' => '' . $media->id,
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertEquals('images/test.jpg', $this->tour->fresh()->mainImage->file);
    }

    /** @test */
    public function tour_titles_must_be_unique()
    {
        $this->loginAs($this->client);

        $otherTour = create('App\Tour');

        $this->assertCount(2, Tour::all());

        $this->updateTour(['title' => $otherTour->title])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function a_tours_stops_can_be_ordered_all_at_once()
    {
        // $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $stop = create('App\TourStop', ['tour_id' => $this->tour, 'order' => 55]);
        $stop2 = create('App\TourStop', ['tour_id' => $this->tour]);
        $stop3 = create('App\TourStop', ['tour_id' => $this->tour]);

        $this->assertEquals([$stop2->id, $stop3->id, $stop->id], $this->tour->stopOrder->toArray());

        $newOrder = [$stop->id, $stop2->id, $stop3->id];
        $data = ['order' => $newOrder];

        $this->put(route('cms.tour.order', ['tour' => $this->tour]), $data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertEquals($newOrder, $this->tour->fresh()->stopOrder->toArray());
    }

    /** @test */
    public function adventure_tour_length_should_be_calculated_on_save()
    {
        $this->loginAs($this->client);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour, 'order' => 2]);
        $stop2->location->update([
            'id' => 2610,
            'address1' => '500 Grand St',       // Grand Vin
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74331877,
            'longitude' => -74.03518617,
        ]);

        $stop1 = create('App\TourStop', ['tour_id' => $this->tour, 'order' => 1,
            'next_stop_id' => $stop2->id]);

        $stop1->location->update([
            'address1' => '77 River St',    // Hoboken Cigars
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.73611847,
            'longitude' => -74.0290305,
        ]);

        $stop1->syncRoutes([
            [
                'next_stop_id' => $stop2->id,
                'route' => [
                    ['lat' => 40.74331877, 'lng' => -74.03518617]
                ],
            ],
        ]);

        $this->tour->update([
            'type' => TourType::ADVENTURE,
            'start_point_id' => $stop1->id,
            'end_point_id' => $stop2->id,
        ]);

        $this->assertTrue($this->tour->fresh()->updateLength());
        $this->assertEquals(0.59, $this->tour->fresh()->length);
    }

    /** @test */
    public function outdoor_tour_length_should_be_calculated_by_its_route_on_save()
    {
        $this->loginAs($this->client);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour, 'order' => 2]);
        $stop1 = create('App\TourStop', ['tour_id' => $this->tour, 'order' => 1,
            'next_stop_id' => $stop2->id]);

        $this->tour->syncRoute([
            ['lat' => 40.74331877, 'lng' => -74.03518617],
            ['lat' => 40.73611847, 'lng' => -74.0290305],
        ]);
        $this->tour->update([
            'type' => TourType::OUTDOOR,
            'start_point_id' => $stop1->id,
            'end_point_id' => $stop2->id,
        ]);

        $this->assertTrue($this->tour->fresh()->updateLength());
        $this->assertEquals(0.59, $this->tour->fresh()->length);
    }

    /** @test */
    public function only_an_admin_can_update_a_tours_in_app_id()
    {
        $this->signIn('admin');

        $this->updateTour(['in_app_id' => 'com.test.yeah'])
            ->assertStatus(200);

        $this->assertEquals('com.test.yeah', $this->tour->fresh()->in_app_id);

        $this->signIn('client');
        $this->tour->update(['user_id' => $this->signInUser->id]);

        $this->updateTour(['in_app_id' => 'other'])
            ->assertStatus(200);

        $this->assertEquals('com.test.yeah', $this->tour->fresh()->in_app_id);
    }
}
