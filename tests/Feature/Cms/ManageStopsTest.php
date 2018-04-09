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
    public function a_stop_can_be_multiple_chocie()
    {
        $this->loginAs($this->business);

        $data = [
            'is_multiple_choice' => true,
        ];

        $this->updateStop(array_merge($this->stop->toArray(), $data))
            ->assertStatus(200)
            ->assertJson($data);
    }
}
