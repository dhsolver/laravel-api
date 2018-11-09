<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\TourStop;
use App\StopChoice;
use App\Media;

class ManageStopsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;
    public $stop;

    public function setUp()
    {
        parent::setUp();

        $this->client = createUser('client');

        $this->tour = create('App\Tour', ['user_id' => $this->client->id]);

        $this->stop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 1]);
    }

    public function stopRoute($name, $withStop = false)
    {
        $id = $this->stop->id;
        if (is_object($withStop)) {
            $id = $withStop->id;
        } elseif ($withStop === false) {
            $id = null;
        }

        return route("cms.stops.$name", [
            'tour' => $this->tour->id,
            'stop' => $id
        ]);
    }

    protected function updateStop($overrides = [])
    {
        $data = array_merge($this->stop->toArray(), $overrides);

        return $this->json('PATCH', $this->stopRoute('update', true), $data);
    }

    /** @test */
    public function a_stop_can_be_updated_by_its_creator()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $data = [
            'title' => 'new title',
            'description' => 'new description',
        ];

        $data = $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function a_stop_cannot_be_updated_by_another_user()
    {
        $this->signIn('client');

        $this->updateStop([
            'title' => 'new title',
        ])->assertStatus(403);
    }

    /** @test */
    public function a_stop_requires_a_title_and_description_to_be_updated()
    {
        $this->loginAs($this->client);

        unset($this->stop['title']);

        $this->updateStop($this->stop->toArray())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function a_user_can_get_all_stops_for_a_tour()
    {
        $this->loginAs($this->client);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);
        $stop3 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 3]);

        $otherTour = create('App\Tour', ['user_id' => $this->client->id]);
        create('App\TourStop', ['tour_id' => $otherTour->id, 'order' => 1, 'title' => 'BADTITLE!']);

        $this->assertCount(4, TourStop::all());

        $this->json('GET', route('cms.stops.index', $this->tour->id))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->stop->title])
            ->assertJsonFragment(['title' => $stop2->title])
            ->assertJsonFragment(['title' => $stop3->title])
            ->assertJsonMissing(['title' => 'BADTITLE!']);
    }

    /** @test */
    public function a_stop_can_be_deleted_by_its_creator()
    {
        $this->withExceptionHandling();

        $this->loginAs($this->client);

        $this->assertCount(1, $this->tour->stops);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(200);

        $this->assertCount(0, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_cannot_be_deleted_by_another_user()
    {
        $this->withExceptionHandling();

        $this->signIn('client');

        $this->assertCount(1, $this->tour->stops);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(403);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_can_be_seen_by_its_creator()
    {
        $this->loginAs($this->client);

        $this->json('GET', $this->stopRoute('show', true))
            ->assertStatus(200)
            ->assertJsonFragment(['description' => $this->stop->description]);
    }

    /** @test */
    public function a_stop_cannot_be_seen_by_another_user()
    {
        $this->signIn('client');

        $this->json('GET', $this->stopRoute('show', true))
            ->assertStatus(403);
    }

    /** @test */
    public function a_stop_can_be_reordered()
    {
        $this->loginAs($this->client);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);
        $stop3 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 3]);

        $this->json('GET', $this->stopRoute('index'))
            ->assertStatus(200)
            ->assertSeeInOrder([$this->stop->title, $stop2->title, $stop3->title]);

        $result = $this->json('PUT', $this->stopRoute('order', $stop3), ['order' => 1]);

        $result->assertStatus(200)
            ->assertSeeInOrder([$stop3->title, $this->stop->title, $stop2->title]);
    }

    /** @test */
    public function a_stop_can_have_an_address()
    {
        $this->withoutExceptionHandling();
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

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJsonFragment($data['location']);
    }

    /** @test */
    public function a_stop_can_have_a_question_and_answer()
    {
        $this->loginAs($this->client);

        $data = [
            'is_multiple_choice' => false,
            'question' => 'Test question?',
            'question_answer' => 'The answer',
            'question_success' => "That's correct!",
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function a_stop_can_be_multiple_choice()
    {
        $this->loginAs($this->client);

        $data = [
            'is_multiple_choice' => true,
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /**
     * Helper function to get the choice array without adding the
     * timestamps (sometimes they fail the tests)
     *
     * @param StopChoice $choice
     * @return array
     */
    public function choiceData(StopChoice $choice)
    {
        return [
            'id' => $choice->id,
            'tour_stop_id' => $choice->tour_stop_id,
            'order' => $choice->order,
            'answer' => $choice->answer,
            'next_stop_id' => $choice->next_stop_id,
        ];
    }

    /** @test */
    public function a_choice_can_be_added_to_a_stop()
    {
        $this->loginAs($this->client);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJson(['data' => $data]);
    }

    /** @test */
    public function a_choice_requires_an_answer()
    {
        $this->loginAs($this->client);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        unset($choice->answer);

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['choices.0.answer']);
    }

    /** @test */
    public function a_choices_next_stop_must_be_exist()
    {
        $this->loginAs($this->client);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = 999;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('choices.0.next_stop_id');
    }

    /** @test */
    public function a_choice_can_be_updated()
    {
        $this->disableExceptionHandling();

        $this->loginAs($this->client);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertEquals($choice->answer, $this->stop->choices->first()->answer);

        $choice->answer = 'new answer';

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.choices')
            ->assertJsonFragment($this->choiceData($choice));
    }

    /** @test */
    public function a_choice_can_be_removed()
    {
        $this->disableExceptionHandling();

        $this->loginAs($this->client);

        $choice1 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice2 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertCount(2, $this->stop->choices);

        $data = [
            'choices' => [
                $choice1->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJson(['data' => $data]);

        $this->assertCount(1, $this->stop->fresh()->choices);
    }

    /** @test */
    public function choices_should_order_themselves()
    {
        $this->loginAs($this->client);

        $choice1 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice2 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertEquals(
            [$choice1->id, $choice2->id],
            $this->stop->fresh()->choices()->pluck('id')->toArray()
        );

        $newChoice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $data = [
            'choices' => [
                $choice1,
                $choice2,
                $newChoice->toArray(),
            ],
        ];

        $resp = $this->updateStop($data)
            ->assertStatus(200);

        $choices = $this->stop->fresh()->choices();
        $this->assertEquals(['1', '2', '3'], $choices->pluck('id')->toArray());
        $this->assertEquals(['1', '2', '3'], $choices->pluck('order')->toArray());
    }

    /** @test */
    public function choices_should_respect_the_submitted_order()
    {
        // $this->disableExceptionHandling();

        $this->loginAs($this->client);

        $choice1 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice2 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice3 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertEquals(
            [$choice1->id, $choice2->id, $choice3->id],
            $this->stop->fresh()->choices()->pluck('id')->toArray()
        );

        $choice2->order = 1;
        $choice1->order = 6;

        $data = [
            'choices' => [
                $choice1,
                $choice2,
                $choice3,
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200);

        $this->assertEquals(
            [$choice2->id, $choice3->id, $choice1->id],
            $this->stop->fresh()->choices()->pluck('id')->toArray()
        );
    }

    /** @test */
    public function a_choice_can_have_a_next_stop()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        $nextStop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $nextStop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.choices')
            ->assertJsonFragment($this->choiceData($choice));

        $this->assertEquals($nextStop->id, $choice->fresh()->next_stop_id);
    }

    /** @test */
    public function a_choice_cannot_have_invalid_next_stops()
    {
        $this->loginAs($this->client);

        $otherTour = create('App\Tour', ['user_id' => $this->client->id]);
        $otherStop = create('App\TourStop', ['tour_id' => $otherTour->id, 'order' => 1]);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $otherStop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['choices.0.next_stop_id']);
    }

    /** @test */
    public function a_choices_next_stop_cannot_be_the_current_stop()
    {
        $this->loginAs($this->client);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $this->stop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['choices.0.next_stop_id']);
    }

    /** @test */
    public function stop_media_can_be_updated()
    {
        $this->loginAs($this->client);

        $media = Media::create([
            'file' => 'images/test.jpg',
            'user_id' => $this->client->id,
            'type' => Media::TYPE_IMAGE,
        ]);

        $data = [
            'main_image_id' => '' . $media->id,
            'image1_id' => '' . $media->id,
            'image2_id' => '' . $media->id,
            'image3_id' => '' . $media->id,
            'intro_audio_id' => '' . $media->id,
            'background_audio_id' => '' . $media->id,
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertEquals('images/test.jpg', $this->stop->fresh()->mainImage->file);
    }

    /** @test */
    public function a_stop_can_have_a_numeric_play_radius()
    {
        $this->loginAs($this->client);

        $data = [
            'play_radius' => 5.32,
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);

        $data = [
            'play_radius' => 'test',
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['play_radius']);
    }

    /** @test */
    public function a_stop_cannot_be_deleted_if_it_is_a_start_point()
    {
        $this->loginAs($this->client);

        $this->tour->update(['start_point_id' => $this->stop->id]);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(422);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_cannot_be_deleted_if_it_is_an_end_point()
    {
        $this->loginAs($this->client);

        $this->tour->update(['end_point_id' => $this->stop->id]);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(422);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_cannot_be_deleted_if_it_is_a_stop_choice_destination()
    {
        $this->loginAs($this->client);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id, 'next_stop_id' => $this->stop->id]);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(422);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_can_have_a_next_stop()
    {
        $this->loginAs($this->client);

        $nextStop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);

        $data = [
            'next_stop_id' => '' . $nextStop->id
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function a_stops_next_stop_must_exist()
    {
        $this->loginAs($this->client);

        $data = [
            'next_stop_id' => '999',
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('next_stop_id');
    }

    /** @test */
    public function a_stop_cannot_have_an_invalid_next_stop()
    {
        $this->loginAs($this->client);

        $otherTour = create('App\Tour', ['user_id' => $this->client->id]);
        $otherStop = create('App\TourStop', ['tour_id' => $otherTour->id, 'order' => 1]);

        $data = [
            'next_stop_id' => '' . $otherStop->id,
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['next_stop_id']);
    }

    /** @test */
    public function a_stops_next_stop_cannot_be_the_current_stop()
    {
        $this->loginAs($this->client);

        $data = [
            'next_stop_id' => $this->stop->id,
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['next_stop_id']);
    }

    /** @test */
    public function a_stop_can_have_routes()
    {
        $this->loginAs($this->client);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);
        $stop3 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 3]);

        $this->loginAs($this->client);

        $data = [
            'routes' => [
                [
                    'next_stop_id' => $stop2->id,
                    'route' => [
                        (object)['lat' => 40.75795412, 'lng' => -71.98552966],
                        (object)['lat' => 41.75795412, 'lng' => -72.98552966],
                        (object)['lat' => 42.75795412, 'lng' => -73.98552966],
                        (object)['lat' => 43.75795412, 'lng' => -74.98552966],
                    ],
                ],
                [
                    'next_stop_id' => $stop3->id,
                    'route' => [
                        (object)['lat' => 43.75795412, 'lng' => -74.98552966],
                        (object)['lat' => 40.75795412, 'lng' => -71.98552966],
                    ],
                ],
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }
}
