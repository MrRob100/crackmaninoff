@extends('layouts.app')

@section('content')

    <div class="contr to-blur">
        <form class="upl" id="song-upload" action="{{ $para }}" method="post" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input id="song-file-input" type="file" name="song" onchange="ch()">
        </form>
        <i class="fa fa-info-circle info-icon" aria-hidden="true"></i>
    </div>

    <div class="to-blur mb-3 mx-3">
        <a href='{{ url('') }}'><p class="o-page">home</p></a>
        @foreach ($pages as $page)
            <a href='{{ $page }}'><p class="o-page">{{ $page }}</p></a>
        @endforeach
    </div>

    <Ctx
        tunes="{{ $t_string }}"
        para="{{ $para }}"
        img-path="{{ env("IMG_PATH") }}"
        storage-path="{{ env("STORAGE_PATH") }}"
    ></Ctx>
    
@endsection

<script>

    //upload
    function ch() {
        $('#song-upload').submit();
    }

</script>
