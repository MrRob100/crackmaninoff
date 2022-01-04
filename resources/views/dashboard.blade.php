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

{{--    <Particles--}}
{{--        id="tsparticles"--}}
{{--        :options="{--}}
{{--            background: {--}}
{{--                color: {--}}
{{--                    value: '#0d47a1'--}}
{{--                }--}}
{{--            },--}}
{{--            fpsLimit: 60,--}}
{{--            interactivity: {--}}
{{--                detectsOn: 'canvas',--}}
{{--                events: {--}}
{{--                    onClick: {--}}
{{--                        enable: true,--}}
{{--                        mode: 'push'--}}
{{--                    },--}}
{{--                    onHover: {--}}
{{--                        enable: true,--}}
{{--                        mode: 'repulse'--}}
{{--                    },--}}
{{--                    resize: true--}}
{{--                },--}}
{{--                modes: {--}}
{{--                    bubble: {--}}
{{--                        distance: 400,--}}
{{--                        duration: 2,--}}
{{--                        opacity: 0.8,--}}
{{--                        size: 40--}}
{{--                    },--}}
{{--                    push: {--}}
{{--                        quantity: 4--}}
{{--                    },--}}
{{--                    repulse: {--}}
{{--                        distance: 200,--}}
{{--                        duration: 0.4--}}
{{--                    }--}}
{{--                }--}}
{{--            },--}}
{{--            particles: {--}}
{{--                color: {--}}
{{--                    value: '#ffffff'--}}
{{--                },--}}
{{--                links: {--}}
{{--                    color: '#ffffff',--}}
{{--                    distance: 150,--}}
{{--                    enable: true,--}}
{{--                    opacity: 0.5,--}}
{{--                    width: 1--}}
{{--                },--}}
{{--                collisions: {--}}
{{--                    enable: true--}}
{{--                },--}}
{{--                move: {--}}
{{--                    direction: 'none',--}}
{{--                    enable: true,--}}
{{--                    outMode: 'bounce',--}}
{{--                    random: false,--}}
{{--                    speed: 6,--}}
{{--                    straight: false--}}
{{--                },--}}
{{--                number: {--}}
{{--                    density: {--}}
{{--                        enable: true,--}}
{{--                        value_area: 800--}}
{{--                    },--}}
{{--                    value: 80--}}
{{--                },--}}
{{--                opacity: {--}}
{{--                    value: 0.5--}}
{{--                },--}}
{{--                shape: {--}}
{{--                    type: 'circle'--}}
{{--                },--}}
{{--                size: {--}}
{{--                    random: true,--}}
{{--                    value: 5--}}
{{--                }--}}
{{--            },--}}
{{--            detectRetina: true--}}
{{--        }"--}}
{{--    />--}}

@endsection

<script>

    //upload
    function ch() {
        $('#song-upload').submit();
    }

</script>
<style>
    #tsparticles {
        position: absolute;
        z-index: 0;
        top: 0;
        width: 100%;
        height: 1000px;
    }
</style>
