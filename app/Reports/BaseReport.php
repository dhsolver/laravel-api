<?php

namespace App\Reports;

use App\Tour;

class BaseReport
{
    /**
     * The tour object.
     *
     * @var \App\Tour
     */
    protected $tour;

    /**
     * Constructor
     * @param \App\Tour $tour
     */
    public function __construct(Tour $tour)
    {
        $this->tour = $tour;
    }
}
