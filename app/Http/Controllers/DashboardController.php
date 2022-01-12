<?php

namespace App\Http\Controllers;

use App\Services\TunesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Models\Page;

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

        ini_set('max_post_size', 0);

        if (!file_exists(public_path(). '/songs/' .$para)) {
            //make it self destruct

            mkdir(public_path(). '/songs/'.$para);
            chmod(public_path(). '/songs/'.$para, 0777);
        }

        //save new page WORK ON THIS

        $p = Page::firstOrNew(['name' => $para === '' ? '/' : $para]);
        $p->save();

        $para_a = $para == "" ? "" : $para."/";

        $tunes_ordered = glob(public_path(). '/songs/'.$para_a.'*.mp3');
        usort($tunes_ordered, function($a, $b) {
            return filemtime($a) < filemtime($b) ? 1 : 0;
        });

        $tunes = [];
        foreach ($tunes_ordered as $tune_ordered) {
            $tunes[] = str_replace(public_path(). '/songs/'.$para_a, '', $tune_ordered);
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
