<?php

namespace App\Http\Controllers;

use App\Services\TunesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    protected $tunesService;

    public function __construct(TunesService $tunesService) {
        $this->tunesService = $tunesService;
    }

    public function index($para = "") {
        $checker = App::make('App\Services\UrlService');
        $checked = $checker->checkUrl($para);

        if (!$checked) {
            return redirect('');
        }

        $p = Page::firstOrNew(['name' => $para === '' ? '/' : $para]);
        $p->save();

        $para_a = $para == "" ? "" : $para."/";

        $files = Storage::disk('s3')->files(env('BUCKET_DIR') . '/public');

        if (isset($_GET['song'])) {
            $tunes = [$para_a . $_GET['song']];
        } else {
            $tunes = [];
            foreach ($files as $file) {
                $to_filter = env('BUCKET_DIR') . '/public/';

                $tunes[] = str_replace($to_filter, '', $file);
            }
        }

        $para = $para == "" ? '-' : $para;

        $t_string = implode(' ', $tunes);

        $pages = Page::all()->pluck('name');

        return view('dashboard', compact('t_string', 'para', 'pages'));
    }

    public function upload(Request $request, $para) {

        $this->tunesService->upload($request, $para);

        return redirect($para);
    }

    public function download()
    {
        try {
            return Response()->download('storage/data/'.$_GET['song']);
        } catch (\exception $e) {
            log::error('error downloading' . $_GET['song'] .' '. $e->getMessage());
        }
    }

    public function delete() {
        $para = $_GET['para'] == '-' ? '' : $_GET['para'].'/';
        try {
            unlink(public_path(). '/songs/'.$para.$_GET['song']);
        } catch (\exception $e) {
            Log::error('delete failed for '. $_GET['song']. ' '.$e->getMessage());
            return "";
        }

        return 'deleted';
    }
}
