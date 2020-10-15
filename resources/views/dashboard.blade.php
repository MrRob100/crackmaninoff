@extends('layouts.app')

@section('content')

    <div class="contr to-blur">
        <form class="upl" id="song-upload" action="{{ $para }}" method="post" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input id="song-file-input" type="file" name="song" onchange="ch()">
        </form>
        <i class="fa fa-info-circle info-icon" aria-hidden="true"></i>

    </div>

    {{-- <span>Pages:</span>
        <a href='{{ url('') }}'><p class="o-page">home</p></a>
        @foreach ($subdirs as $subdir)
            <a href='{{ $subdir[0] }}'><p class="o-page">{{ $subdir[0] }}</p></a>
        @endforeach --}}
    <Ctx
        tunes="{{ $t_string }}"
        para="{{ $para }}"
    ></Ctx>

@endsection

<script>

    //upload
    function ch() {
        $('#song-upload').submit();
    }

</script>
