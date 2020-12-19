<?php

namespace App\Http\Controllers;

use App\Services\TunesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Models\Page;
use App\Models\Tune;

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

        if (!file_exists(env('STORAGE_PATH').$para)) {
            //make it self destruct

            dd(env('STORAGE_PATH').$para);

//            mkdir(env('STORAGE_PATH').$para);
            chmod(env('STORAGE_PATH').$para, 0777);
        }

        //save new page WORK ON THIS

        $p = Page::firstOrNew(['name' => $para === '' ? '/' : $para]);
        $p->save();

        $para_a = $para == "" ? "" : $para."/";

        $tunes_ordered = glob(env('STORAGE_PATH').$para_a.'*.mp3');
        usort($tunes_ordered, function($a, $b) {
            return filemtime($a) < filemtime($b) ? 1 : 0;
        });

        $tunes = [];
        foreach ($tunes_ordered as $tune_ordered) {
            $tunes[] = str_replace(env('STORAGE_PATH').$para_a, '', $tune_ordered);
        }

        $para = $para == "" ? '-' : $para;

        $t_string = implode(' ', $tunes);

        $pages = Page::all()->whereIn('id', Tune::pluck('page_id'))->pluck('name');

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
        } catch (exception $e) {
            log::error('error downloading' . $_GET['song'] .' '. $e->getMessage());
        }
    }

    public function delete() {
        $para = $_GET['para'] == '-' ? '' : $_GET['para'].'/';
        try {
            unlink('storage/data/'.$para.$_GET['song']);
        } catch (exception $e) {
            Log::error('delete failed for '. $_GET['song']. ' '.$e->getMessage());
            return "";
        }

        //remove db record
        Tune::destroy(Tune::where('name', $_GET['song'])->first()->id);

        return 'deleted';
    }

    public function getMarker() {

        if ($_GET['se'] === 's') {
            $which = 'start';
        } elseif ($_GET['se'] === 'e') {
            $which = 'end';
        } else {
            //throw exception
            $which = null;
        }

        $position = Tune::where('name', $_GET['name'])->get($which);

        return $position;
    }

    public function setMarker() {

        if ($_GET['se'] === 's') {
            $which = 'start';
        } elseif ($_GET['se'] === 'e') {
            $which = 'end';
        } else {
            $which = null;
        }

        $page_id = Page::where([
            ['name', isset($_GET['page']) ? $_GET['page'] : '/'],
        ])->get('id')->first()->id;

        $t = Tune::where([
            ['name', $_GET['name']]
        ])->first();

        Tune::where([
            ['name', $_GET['name']]
            ])->update(
            [
                $which => $_GET['value'],
                'page_id' => $page_id
            ]
        );
    }

    public function ctx() {

        $items = scandir('storage/data/');

        array_shift($items);
        array_shift($items);

        $tunes = [];
        foreach ($items as $item) {
            if (strpos($item, '.mp3') !== false) {
                $tunes[] = $item;
            }
        }

        $t_string = implode(' ', $tunes);

        return view('ctx', compact('t_string'));
    }

    public function phaser() {
        return view('phaser');
    }

    public function allpass() {
        return view('allpass');
    }


}
