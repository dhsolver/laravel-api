<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\Mobile\Resources\TourResource;
use App\TourStop;

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
        factory(Tour::class, 3)->create();

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

        factory(Tour::class, 3)->create();
        $tour = factory(Tour::class)->create([
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

        $tour = factory(Tour::class)->create([
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
}