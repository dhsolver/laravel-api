<?php

namespace App\Points;

use App\UserScore;

interface PointsCalculator
{
    public function getPar();

    public function scoreQualifiesForTrophy($scoreCard);

    public function getPoints(UserScore $scoreCard);

    public function getTotalStops();
}
