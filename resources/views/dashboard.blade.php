@extends('layouts.app')

@section('content')
    @if(auth()->user())
        <div class="contr to-blur">
            <form class="upl" id="song-upload" action="{{ $para }}" method="post" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input id="song-file-input" type="file" name="song" onchange="ch()">
            </form>
            <i class="fa fa-info-circle info-icon" aria-hidden="true"></i>
        </div>
    @endif

    <div class="to-blur mb-3 mx-3">
        <a href='{{ url('') }}'><p class="o-page">home</p></a>
        @foreach ($pages as $page)
            <a href='{{ $page }}'><p class="o-page">{{ $page }}</p></a>
        @endforeach
    </div>

    <player
        auth="{{ (bool) auth()->user() }}"
        tunes="{{ $t_string }}"
        para="{{ $para }}"
        public="{{ env('APP_ENV') === 'local' ? '' : '/public' }}"
        bucket="{{ env('STORAGE_URL') }}"
    >
    </player>

@endsection

<script>
    function ch() {
        $("#song-upload").submit();
    }
</script>
