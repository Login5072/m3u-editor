<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\CustomPlaylist;
use App\Models\MergedPlaylist;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Event\Runtime\PHP;
use LaLit\Array2XML;

class EpgGenerateController extends Controller
{
    public function __invoke(string $uuid)
    {
        // Fetch the playlist
        $playlist = Playlist::where('uuid', $uuid)->first();
        if (!$playlist) {
            $playlist = MergedPlaylist::where('uuid', $uuid)->first();
        }
        if (!$playlist) {
            $playlist = CustomPlaylist::where('uuid', $uuid)->firstOrFail();
        }

        // Generate a filename
        $filename = Str::slug($playlist->name) . '.xml';

        // Output the XML header
        echo '<?xml version="1.0" encoding="UTF-8"?>
<tv generator-info-name="Generated by ' . env('APP_NAME') . '" generator-info-url="' . url('') . '">';
        echo PHP_EOL;

        // Setup the channels
        $where = [
            ['playlist_id', $playlist->id],
            ['enabled', true],
        ];
        $channels = Channel::where($where)->cursor();
        foreach ($channels as $channel) {
            // Output the <channel> tag
            if ($channel->epgChannel) {
                $epgData = $channel->epgChannel;
                echo '  <channel id="' . $epgData->channel_id . '">' . PHP_EOL;
                echo '    <display-name lang="' . $epgData->lang . '">' . $epgData->name . '</display-name>';
                if ($epgData->icon) {
                    echo PHP_EOL . '    <icon src="' . $epgData->icon . '"/>';
                }
                echo PHP_EOL . '  </channel>' . PHP_EOL;
            }
        }

        // @TODO: get the epg data...

        // Close it out
        echo '</tv>';

        die();






        // Get ll active channels
        return response()->stream(
            function () use ($playlist) {
                // Output the XML header
                echo '<?xml version="1.0" encoding="UTF-8"?>
<tv generator-info-name="Generated by ' . env('APP_NAME') . '" generator-info-url="' . url('') . '">';
                echo PHP_EOL;

                // Setup the channels
                $where = [
                    ['playlist_id', $playlist->id],
                    ['enabled', true],
                ];
                $channels = Channel::where($where)->cursor();
                foreach ($channels as $channel) {
                    // Output the <channel> tag
                    if ($channel->epgChannel) {
                        $epgData = $channel->epgChannel;
                        echo '  <channel id="' . $epgData->channel_id . '">' . PHP_EOL;
                        echo '    <display-name lang="' . $epgData->lang . '">' . $epgData->name . '</display-name>';
                        if ($epgData->icon) {
                            echo PHP_EOL . '    <icon src="' . $epgData->icon . '"/>';
                        }
                        echo PHP_EOL . '  </channel>' . PHP_EOL;
                    }
                }

                // ...

                // Close it out
                echo '</tv>';
            },
            200,
            [
                'Access-Control-Allow-Origin' => '*',
                'Content-Disposition' => "attachment; filename=$filename",
                'Content-Type' => 'application/xml'
            ]
        );
    }
}
