<?php

namespace App\Points;

use App\ScoreCard;

interface IPointsCalculator
{
    public function getPar();

    public function scoreQualifiesForTrophy($scoreCard);

    public function getPoints(ScoreCard $scoreCard);

    public function getTotalStops();
}
