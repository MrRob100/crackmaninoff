@extends('layouts.app')

@section('content')

    <h1>RUNNING</h1>
    <p id="speedx">x: 0</p>
    <p id="speedy">y: 0</p>
    <p id="speedz">z: 0</p>
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

                            document.getElementById('speedx').innerText = "x: " + speedX;
                            document.getElementById('speedy').innerText = "y: " + speedY;
                            document.getElementById('speedz').innerText = "z: " + speedZ;


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
