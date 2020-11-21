<?php

namespace App\Http\Controllers;

use App\Services\TunesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Services\UrlService;
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

        if (!file_exists('storage/data/'.$para)) {
            //make it self destruct
            mkdir('storage/data/'.$para);
            chmod('storage/data/'.$para, 0777);
        }

        $para_a = $para == "" ? "" : $para."/";

        //save new page WORK ON THIS
        $p = Page::firstOrNew(['name' => $para]);
        $p->save();

        $tunes_ordered = glob('storage/data/'.$para_a.'*.mp3');
        usort($tunes_ordered, function($a, $b) {
            return filemtime($a) < filemtime($b);
        });

        $tunes = [];
        foreach ($tunes_ordered as $tune_ordered) {
            $tunes[] = str_replace('storage/data/'.$para_a, '', $tune_ordered);
        }

        $para = $para == "" ? '-' : $para;

        $t_string = implode(' ', $tunes);

        return view('dashboard', compact('t_string', 'para'));
    }

    public function upload(Request $request, $para) {

        $this->tunesService->upload($request, $para);

        return redirect($para);
    }

    public function dl() {
        try {
            // return Storage::download(app_path('../public/storage/data/'.$_GET['song']));
            return Response()->download('storage/data/'.$_GET['song']);
        } catch (exception $e) {
            dump($e);
        }
    }

    public function delete() {

        $para = $_GET['para'] == '-' ? '' : $_GET['para'].'/';

        $markers = json_decode(file_get_contents('../public/data/markerData.json'), true);

        unset($markers[$_GET['song']]);

        try {
            unlink('storage/data/'.$para.$_GET['song']);
        } catch (exception $e) {
            //log exep
            return "";
        }
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

        $position = DB::table('tunes')->where('name', $_GET['name'])->get($which);

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

        $page_id = DB::table('pages')->where([
            ['name', isset($_GET['page']) ? $_GET['page'] : '/'],
        ])->get('id')->first()->id;

        DB::table('tunes')->where([
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
