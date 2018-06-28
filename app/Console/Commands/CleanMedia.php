<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Media;
use App\Tour;
use Carbon\Carbon;

class CleanMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // get all media where id doesn't exist in tour_stops.image1/2/3/main/audio1/2
        $ids = array_merge(
            $this->getStopIds('main_image_id'),
            $this->getStopIds('image1_id'),
            $this->getStopIds('image2_id'),
            $this->getStopIds('image3_id'),
            $this->getStopIds('intro_audio_id'),
            $this->getStopIds('background_audio_id'),

            $this->getTourIds('intro_audio_id'),
            $this->getTourIds('background_audio_id'),
            $this->getTourIds('main_image_id'),
            $this->getTourIds('image1_id'),
            $this->getTourIds('image2_id'),
            $this->getTourIds('image3_id'),
            $this->getTourIds('trophy_image_id'),
            $this->getTourIds('start_image_id'),
            $this->getTourIds('end_image_id'),
            $this->getTourIds('pin_image_id')
        );

        // get matching media files that are more than a day old
        // to prevent removing media that might be currently used
        $mediaFiles = Media::whereNotIn('id', $ids)
            ->where('created_at', '<=', Carbon::now()->subDays(1)->toDateTimeString())
            ->get();

        echo 'Found ' . $mediaFiles->count() . " objects not being used\n";

        $this->deleteFilesFromStorage($mediaFiles);

        Media::whereIn('id', $mediaFiles->pluck('id'))->delete();

        echo "Media files cleaned.\r\n";
    }

    public function deleteFilesFromStorage($media)
    {
        foreach ($media as $file) {
            \Storage::delete($file->file);
        }
    }

    public function queryIds($table, $field)
    {
        return \DB::table($table)->select($field)->whereNotNull($field)->get()->map(function ($item) use ($field) {
            return $item->$field;
        })->toArray();
    }

    public function getStopIds($field)
    {
        return $this->queryIds('tour_stops', $field);
    }

    public function getTourIds($field)
    {
        return $this->queryIds('tours', $field);
    }
}
