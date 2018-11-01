<?php

namespace App;

class TourAuditor
{
    /**
     * The tour to be audited.
     *
     * @var \App\Tour
     */
    public $tour;

    /**
     * The resulting error messages.
     *
     * @var array
     */
    public $errors = [];

    /**
     * @param \App\Tour $tour
     */
    public function __construct($tour)
    {
        $this->tour = $tour;
        $this->errors = [];
    }

    /**
     * Run an audit check on the Tour.
     *
     * @return boolean
     */
    public function run()
    {
        $this->common();

        switch ($this->tour->type) {
            case TourType::INDOOR:
                $this->indoor();
                break;
            case TourType::ADVENTURE:
                $this->adventure();
                break;
            default:
                $this->outdoor();
                break;
        }

        if (count($this->errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Append to the errors array.
     *
     * @param string $error
     * @return void
     */
    public function error($error)
    {
        array_push($this->errors, $error);
    }

    /**
     * Check common fields.
     *
     * @return void
     */
    public function common()
    {
        if (empty($this->tour->title)) {
            $this->error('The tour has no title.');
        }

        if (empty($this->tour->description)) {
            $this->error('The tour has no description.');
        }

        if (empty($this->tour->main_image_id)) {
            $this->error("The tour doesn't have a main image.");
        }

        if (empty($this->tour->location) || ! $this->tour->location->hasCoordinates()) {
            $this->error("The tour doesn't have a valid location.");
        }

        if ($this->tour->stops()->count() < 1) {
            $this->error("The tour must have at least one stop.");
        }

        foreach ($this->tour->stops as $stop) {
            if (empty($stop->title)) {
                $this->error("The stop #{$stop->order} has no title.");
            }

            if (empty($stop->description)) {
                $this->error("The stop \"{$stop->title}\" has no description.");
            }

            if (empty($stop->location) || ! $stop->location->hasCoordinates()) {
                $this->error("The stop \"{$stop->title}\"  doesn't have a valid location.");
            }
        }
    }

    /**
     * Check indoor tour specifics.
     *
     * @return void
     */
    public function indoor()
    {
    }

    /**
     * Check outdoor tour specifics.
     *
     * @return void
     */
    public function outdoor()
    {
    }

    /**
     * Check adventure tour specifics.
     *
     * @return void
     */
    public function adventure()
    {
    }
}
