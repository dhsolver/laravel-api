<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\TourStop;
use App\StopChoice;

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

        $this->loginAs($this->business);

        $data = $this->updateStop([
            'title' => 'new title',
            'description' => 'new description',
        ])->assertStatus(200)
            ->assertSee('new title')
            ->assertSee('new description');
    }

    /** @test */
    public function a_stop_cannot_be_updated_by_another_user()
    {
        $this->signIn('business');

        $this->updateStop([
            'title' => 'new title',
        ])->assertStatus(403);
    }

    /** @test */
    public function a_stop_requires_a_title_description_and_type_to_be_updated()
    {
        $this->loginAs($this->business);

        unset($this->stop['title'], $this->stop['description'], $this->stop['location_type']);

        $this->updateStop($this->stop->toArray())
            ->assertStatus(422)
            ->assertSee('title')
            ->assertSee('description')
            ->assertSee('location_type');
    }

    /** @test */
    public function a_user_can_get_all_stops_for_a_tour()
    {
        $this->loginAs($this->business);

        $stop2 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);
        $stop3 = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 3]);

        $otherTour = create('App\Tour', ['user_id' => $this->business->id]);
        create('App\TourStop', ['tour_id' => $otherTour->id, 'order' => 1, 'title' => 'BADTITLE!']);

        $this->assertCount(4, TourStop::all());

        $this->json('GET', route('cms.stops.index', $this->tour->id))
            ->assertStatus(200)
            ->assertSee($this->stop->title)
            ->assertSee($stop2->title)
            ->assertSee($stop3->title)
            ->assertDontSee('BADTITLE!');
    }

    /** @test */
    public function a_stop_can_be_deleted_by_its_creator()
    {
        $this->withExceptionHandling();

        $this->loginAs($this->business);

        $this->assertCount(1, $this->tour->stops);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(204);

        $this->assertCount(0, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_cannot_be_deleted_by_another_user()
    {
        $this->withExceptionHandling();

        $this->signIn('business');

        $this->assertCount(1, $this->tour->stops);

        $this->json('DELETE', $this->stopRoute('destroy', true))
            ->assertStatus(403);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }

    /** @test */
    public function a_stop_can_be_seen_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->json('GET', $this->stopRoute('show', true))
            ->assertStatus(200)
            ->assertSee($this->stop->description);
    }

    /** @test */
    public function a_stop_cannot_be_seen_by_another_user()
    {
        $this->signIn('business');

        $this->json('GET', $this->stopRoute('show', true))
            ->assertStatus(403);
    }

    /** @test */
    public function a_stop_can_be_reordered()
    {
        $this->loginAs($this->business);

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
        $this->loginAs($this->business);

        $data = [
            'address1' => md5('123 Elm St.'),
            'address2' => md5('APT 805'),
            'city' => md5('New York'),
            'state' => 'NY',
            'zipcode' => '10001',
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJson($data);
    }

    /** @test */
    public function a_stop_can_have_coordinates()
    {
        $this->loginAs($this->business);

        $data = [
            'latitude' => 23.5235,
            'longitude' => -35.325235,
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJson($data);
    }

    /** @test */
    public function stop_coordinates_must_be_valid()
    {
        $this->loginAs($this->business);

        $data = [
            'latitude' => 293582039,
            'longitude' => 'test',
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'latitude' => ['The latitude format is invalid.'],
                    'longitude' => ['The longitude format is invalid.'],
                ]
            ]);
    }

    /** @test */
    public function a_stop_can_have_a_question_and_answer()
    {
        $this->loginAs($this->business);

        $data = [
            'is_multiple_choice' => false,
            'question' => 'Test question?',
            'question_answer' => 'The answer',
            'question_success' => "That's correct!",
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJson($data);
    }

    /** @test */
    public function a_stop_can_be_multiple_choice()
    {
        $this->loginAs($this->business);

        $data = [
            'is_multiple_choice' => true,
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJson($data);
    }

    /** @test */
    public function a_choice_can_be_added_to_a_stop()
    {
        $this->loginAs($this->business);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    /** @test */
    public function a_choice_requires_an_answer()
    {
        $this->loginAs($this->business);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        unset($choice->answer);

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJson(['errors' => ['choices.0.answer' => ['The choices.0.answer field is required.']]]);
    }

    /** @test */
    public function a_choices_next_stop_must_be_exist()
    {
        $this->loginAs($this->business);

        $choice = make(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = 999;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $this->updateStop($data)
            ->assertStatus(422)
            ->assertJson(['errors' => ['choices.0.next_stop_id' => ['The selected choices.0.next_stop_id is invalid.']]]);
    }

    /** @test */
    public function a_choice_can_be_updated()
    {
        $this->disableExceptionHandling();

        $this->loginAs($this->business);

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
            ->assertJson($data);
    }

    /** @test */
    public function a_choice_can_be_removed()
    {
        $this->disableExceptionHandling();

        $this->loginAs($this->business);

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
            ->assertJson($data);

        $this->assertCount(1, $this->stop->fresh()->choices);
    }

    /** @test */
    public function choices_should_order_themselves()
    {
        $this->loginAs($this->business);

        $choice1 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice2 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertEquals(
            [$choice1->id, $choice2->id],
            $this->stop->fresh()->choices()->ordered()->pluck('id')->toArray()
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

        $choices = $this->stop->fresh()->choices()->ordered();
        $this->assertEquals(['1', '2', '3'], $choices->pluck('id')->toArray());
        $this->assertEquals(['1', '2', '3'], $choices->pluck('order')->toArray());
    }

    /** @test */
    public function choices_should_respect_the_submitted_order()
    {
        // $this->disableExceptionHandling();

        $this->loginAs($this->business);

        $choice1 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice2 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);
        $choice3 = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $this->assertEquals(
            [$choice1->id, $choice2->id, $choice3->id],
            $this->stop->fresh()->choices()->ordered()->pluck('id')->toArray()
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
            $this->stop->fresh()->choices()->ordered()->pluck('id')->toArray()
        );
    }

    /** @test */
    public function a_choice_can_have_a_next_stop()
    {
        $this->loginAs($this->business);

        $nextStop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $nextStop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $resp = $this->updateStop($data)
            ->assertStatus(200)
            ->assertJson($data);

        $this->assertEquals($nextStop->id, $choice->fresh()->next_stop_id);
    }

    /** @test */
    public function a_choice_cannot_have_invalid_next_stops()
    {
        $this->loginAs($this->business);

        $otherTour = create('App\Tour', ['user_id' => $this->business->id]);
        $otherStop = create('App\TourStop', ['tour_id' => $otherTour->id, 'order' => 1]);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $otherStop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $resp = $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['choices.0.next_stop_id']);
    }

    /** @test */
    public function a_choices_next_stop_cannot_be_the_current_stop()
    {
        $this->loginAs($this->business);

        $choice = create(StopChoice::class, ['tour_stop_id' => $this->stop->id]);

        $choice->next_stop_id = $this->stop->id;

        $data = [
            'choices' => [
                $choice->toArray(),
            ],
        ];

        $resp = $this->updateStop($data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['choices.0.next_stop_id']);
    }
}
