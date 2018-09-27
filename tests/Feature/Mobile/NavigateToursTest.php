<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\Mobile\Resources\TourResource;
use App\TourStop;
use App\Review;

class ViewToursTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function a_mobile_user_can_get_a_paginated_list_of_all_published_tours()
    {
        factory(Tour::class, 3)->states('published')->create();

        $tour = new TourResource(Tour::first());

        $this->signIn('user');

        $this->getJson('/mobile/tours')
            ->assertJsonStructure([
                'data' => ['*' => array_keys($tour->toArray(null))],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
            ])
            ->assertJsonCount(3, 'data')
            ->assertStatus(200);
    }

    /** @test */
    public function a_mobile_user_can_search_available_tours_by_keyword()
    {
        $keyword = 'uniquestring';

        factory(Tour::class, 3)->states('published')->create();
        $tour = factory(Tour::class)->states('published')->create([
            'title' => "Blah blah $keyword blah",
        ]);

        $tour = new TourResource($tour);

        $this->signIn('user');

        $this->getJson("/mobile/tours?search=$keyword")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => array_keys($tour->toArray(null))],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => $tour->title]);
    }

    /** @test */
    public function a_mobile_user_can_get_a_single_tour_with_all_stops_and_routes()
    {
        $this->signIn('user');

        $tour = factory(Tour::class)->states('published')->create([
            'title' => 'Test Tour',
        ]);

        factory(TourStop::class, 10)->create([
            'tour_id' => $tour->id,
        ]);

        $tourData = new TourResource($tour);

        $this->getJson("/mobile/tours/{$tour->id}")
            ->assertStatus(200)
            ->assertJsonStructure([
                'tour' => array_keys($tourData->toArray(null)),
                'stops',
                'route',
            ])
            ->assertJsonCount(10, 'stops')
            ->assertJsonFragment(['title' => $tour->title]);
    }

    /** @test */
    public function a_mobile_user_can_only_see_published_tours()
    {
        factory(Tour::class, 1)->create();
        factory(Tour::class, 3)->states('published')->create();

        $this->signIn('user');

        $this->getJson('/mobile/tours')
            ->assertJsonCount(3, 'data')
            ->assertStatus(200);
    }

    /** @test */
    public function a_tour_listing_should_contain_ratings()
    {
        factory(Tour::class, 3)->states('published')->create();

        $this->signIn('user');

        $this->getJson('/mobile/tours')
            ->assertJsonFragment(['rating' => 0])
            ->assertStatus(200);
    }

    /** @test */
    public function tour_info_endpoint_should_show_the_latest_reviews()
    {
        $this->withoutExceptionHandling();

        $this->signIn('user');

        $tour = factory(Tour::class)->states('published')->create();

        factory(Review::class, 3)->create(['tour_id' => $tour->id]);
        $this->getJson('/mobile/tours/' . $tour->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'latest_reviews');
    }

    /** @test */
    public function latest_reviews_should_only_contain_actual_reviews()
    {
        $this->withoutExceptionHandling();

        $this->signIn('user');

        $tour = factory(Tour::class)->states('published')->create();

        factory(Review::class, 3)->create(['tour_id' => $tour->id]);
        factory(Review::class)->create(['tour_id' => $tour->id, 'review' => null]);
        $this->getJson('/mobile/tours/' . $tour->id)
            ->assertStatus(200)
            ->assertJsonCount(3, 'latest_reviews');
    }
}
