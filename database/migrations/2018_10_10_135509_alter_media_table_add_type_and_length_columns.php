<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Media;
use App\Audio\AudioProcessor;

class AlterMediaTableAddTypeAndLengthColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('type', 25)->nullable()->after('id');
            $table->unsignedInteger('length')->default(0)->after('user_id');
        });

        \DB::table('media')->whereRaw("file like '%.jpg'")->update(['type' => 'image']);
        \DB::table('media')->whereRaw("file like '%.png'")->update(['type' => 'icon']);
        \DB::table('media')->whereRaw("file like '%.mp3'")->update(['type' => 'audio']);

        if (app()->environment('production')) {
            // handle calculating length from s3 for production server
            $ffprobe = app()->make(AudioProcessor::class);
            foreach (Media::where('type', 'audio')->get() as $media) {
                try {
                    $length = $ffprobe->getDuration($media->path);
                    $media->update(['length' => $length]);
                } catch (\Exception $ex) {
                    continue;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['type', 'length']);
        });
    }
}
