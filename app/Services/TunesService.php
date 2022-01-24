<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TunesService
{
    /*
        .wav = 'audio/x-wav'
        .mp3 = 'audio/mpeg'
        .aif = 'audio/x-aiff'
    */
    public function upload($request, $para)
    {
        return $this->saveFile($request, $para);
    }

    public function compressConvertFile($para, $song_name, $typ)
    {
        if ($para === '-' || $para === '') {
            $subdir = '';
        } else {
            $subdir = $para . "/";
        }

        $dir = env('APP_PATH') . public_path() . '/songs/' . $subdir;
        $o_path = env('APP_PATH') . env('FFMPEG_PATH');

        //mp3 compression
        if ($typ === 'audio/mpeg') {

            exec($o_path . ' -i ' . $dir . $song_name . ' -ab 110k ' . $dir . '_' . $song_name, $o, $r);

            //deletes raw if compression worked
            if ($r === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('mp3 compression for '.$song_name.' failed. Code: '.$r.' fullpath: '.$dir . $song_name);
                Log::info($o_path . ' -i ' . $dir . $song_name . ' -ab 110k ' . $dir . '_' . $song_name);
                return false;
            }

            //wav conversion
        } elseif ($typ === 'audio/x-wav') {
            exec($o_path. ' -i ' . $dir . $song_name . ' -f mp3 ' . $dir . '_' . str_replace('.wav', '', $song_name) . '.mp3', $oc, $rc);

            //deletes raw if conversion worked
            if ($rc === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('wav conversion for '.$song_name.' failed');
                return false;
            }
        } elseif ($typ === 'audio/x-aiff') {

            // ffmpeg -i sauce.aif -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 sauce.mp3
            exec($o_path. ' -i ' . $dir . $song_name . ' -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 ' . $dir . '_' . str_replace('.aif', '', $song_name) . '.mp3', $ao, $ac);
            //deletes raw if conversion worked
            if ($ac === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('aiff conversion for '.$song_name.' failed');
                return false;
            }
        }

        return true;
    }

    public function saveFile($request, $para): bool
    {
        $file = $request->file('song');
        $typ = $file->getMimeType();

        //make sure propper validation somewhere
        if ($typ === 'audio/mpeg' || $typ === 'audio/x-wav' || $typ === 'audio/x-aiff') {

            //removes spaces in name
            $song_name = str_replace(" ", "_", $file->getClientOriginalName());

            $request->file('song')->storePubliclyAs(env('BUCKET_DIR') . '/public/' . ($para === '-' ? '' : $para) . '', $song_name, 's3');
            return true;
        } else {
            Log::warning('wrong file type: '.$typ);
            return false;
        }
    }
}
