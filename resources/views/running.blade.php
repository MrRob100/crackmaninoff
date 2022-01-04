@extends('layouts.app')

@section('content')

    <h1>RUNNING</h1>
    <p id="speedx">ax: 0</p>
    <p id="speedy">ay: 0</p>
    <p id="speedz">az: 0</p>
    <button onclick="request()">Permission</button>
    <button onclick="music()">Music</button>

@endsection

<script>

    function request() {
        if (typeof DeviceMotionEvent.requestPermission === 'function') {
            DeviceMotionEvent.requestPermission()
                .then(permissionState => {
                    if (permissionState === 'granted') {

                        var lastTimestamp;
                        var speedX = 0, speedY = 0, speedZ = 0;
                        window.addEventListener('devicemotion', function(event) {
                            var currentTime = new Date().getTime();
                            if (lastTimestamp === undefined) {
                                lastTimestamp = new Date().getTime();
                                return; //ignore first call, we need a reference time
                            }
                            //  m/s² / 1000 * (miliseconds - miliseconds)/1000 /3600 => km/h (if I didn't made a mistake)
                            speedX += event.acceleration.x / 1000 * ((currentTime - lastTimestamp)/1000)/3600;
                            speedY += event.acceleration.y / 1000 * ((currentTime - lastTimestamp)/1000)/3600;
                            speedZ += event.acceleration.z / 1000 * ((currentTime - lastTimestamp)/1000)/3600;

                            document.getElementById('speedx').innerText = "ax: " + event.acceleration.x;
                            document.getElementById('speedy').innerText = "ay: " + event.acceleration.y;
                            document.getElementById('speedz').innerText = "az: " + event.acceleration.z;

                            //... same for Y and Z
                            lastTimestamp = currentTime;

                        }, false);

                    }
                })
                .catch(console.error);
        } else {
            console.log('the else');
        }
    }


    function music() {
        console.log('music');
    }


</script>
