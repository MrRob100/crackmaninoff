<template>
    <div v-if="tunes">
        <div class="switcher row to-blur">
            <div>
                <label class="switch">
                    <input v-model="playlist" type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="col-5">
                <p v-if="playlist" class="pr">Repeat / <strong>Playlist</strong></p>
                <p v-else class="pr"><strong>Repeat</strong> / Playlist</p>
            </div>
        </div>

        <div class="slither"
             v-for="(tune, index) in tunesFormatted"
             :key="tune"
        >
            <div v-if="!deleted" :id="'slither-' + index" class="slither-class">
                <small v-if="auth" @click="deleteSong" class="delete-button to-blur">DELETE</small>
                <div
                    class="stack-house to-blur"
                    :class='"no-blur-" + index'
                >
                    <div
                        @click="play(tune, index)"
                        :id='"stack-" + index'
                        class="stack-slice stack-bottom"
                    >
                        <div class="inln-btn">
                            <h3 class="false-shift name-element" :id="'name_loading_' + index">{{ tune }}</h3>
                        </div>

                        <tune-crop
                            @tuneCropMounted="tuneCropMounted"
                            :id='"tc-"+index'
                            :setting='index'
                            :name='tune'
                        ></tune-crop>
                        <canvas class="canv" :id='"canvas-"+index'></canvas>
                        <a :href="dlref"
                        >
                            <button
                                class="dld"
                                v-on:click="download">
                                <img data-toggle="tooltip" title="download" class="crack-icon dl-icon"
                                     :src="public + '/images/dld.png'">
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="control-box">
            <div class="crow ctop-row">
                <div class="close-container" v-for="(tune, index) in tunesFormatted" :key="tune">
                    <i :id='"modal-close-"+index' class="fa fa-close modal-close" aria-hidden="true"></i>
                </div>

                <div class="fx-container speed-container">
                    <input class="fx speed-control" type="range" min="0.5" max="1.5" step="0.01" value="1">
                </div>
                <div class="fx-container reverb-container">
                    <input class="fx reverb-control" type="range" min="0" max="1" step="0.01" value="0">
                </div>
                <div class="fx-container filter-container">
                    <input class="fx filter-control" type="range" min="0" max="20000" step="10" value="20000">
                </div>
                <div class="fx-container mod-container">
                    <input class="fx mod-control" type="range" min="0" max="1" step="0.01" value="0">
                </div>
            </div>
            <div class="bottom-container">
                <div class="crow cbottom-row">
                    <span class="font-weight-bold speed-value">0</span>
                    <span class="font-weight-bold reverb-value">0</span>
                    <span class="font-weight-bold filter-value">0</span>
                    <span class="font-weight-bold mod-value">0</span>
                </div>
                <div class="crow cbottom-row">
                    <span class="font-weight-bold fx-label">Speed</span>
                    <span class="font-weight-bold fx-label">Reverb</span>
                    <span class="font-weight-bold fx-label">Filter</span>
                    <span class="font-weight-bold fx-label">Modulation</span>
                </div>
            </div>
            <div class="crow cstop" v-for="(tune, index) in tunesFormatted" :key="tune">
                <button class="font-weight-bold" :id='"stbutton-"+index'>Stop</button>
            </div>
        </div>
    </div>
</template>

<script>

import Meths from '../meths.js';
import Layout from '../layoutChanges.js';

export default {

    props: ["tunes", "para", "auth", "public", "bucket"],

    data: function() {
        return {
            amt: 0,
            ableToPlay: true,
            convolver: {},
            convolverGain: {},
            ctx: null,
            deleted: false,
            dlref: "",
            dlding: false,
            filter: {},
            first: true,
            init: true,
            initSource: {},
            gain: {},
            loaded: false,
            playlist: false,
            playable: true,
            playFrom: null,
            playTo: null,
            playing: false,
            masterCompression: {},
            myBuffer: null,
            notch: {},
            run: false,
            src: null,
            stopClicked: false,
            screenWidth: "",
            tunesFormatted: {},
        }
    },

    mounted() {
        if (this.para === "-") {
            this.dlref = window.location.origin + "/public/download?song=" + this.name;
        } else {
            this.dlref = window.location.origin + "/public/download?song=" + this.para + "/" + this.name;
        }
        this.tunesFormatted = this.tunes.split(" ");

        this.nameTrim();
        this.canvasWidth();
        window.addEventListener("resize", this.nameTrim);
        window.addEventListener("resize", this.canvasWidth);

        let sourceData = [];
        this.tunesFormatted.forEach(function() {
            sourceData.push(null);
        });

        this.src = sourceData;

        let myBufferData = [];
        this.tunesFormatted.forEach(function() {
            myBufferData.push(null);
        });

        this.myBuffer = myBufferData;

        let playFromData = [];
        this.tunesFormatted.forEach(function() {
            playFromData.push(0);
        });

        this.playFrom = playFromData;
    },

    methods: {
        tuneCropMounted: function() {
            let playToData = [];
            this.tunesFormatted.forEach(function(tune, index) {
                let end = Meths.findMarker(index, "end") / window.innerWidth;
                Meths.findMarker(index, "end");
                playToData.push(end);
            });

            this.playTo = playToData;
        },
        createCtx: function() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const audioCtx = new AudioContext();
            this.ctx = audioCtx;

            this.convolver = this.ctx.createConvolver();
            this.convolverGain = this.ctx.createGain();

            this.gain = this.ctx.createGain();
            this.filter = this.ctx.createBiquadFilter();
            this.notch = this.ctx.createBiquadFilter();

            this.filter.type = "lowpass";
            this.filter.frequency.value = 20000;

            this.notch.type = 'notch';
            this.notch.frequency.value = 100;
            this.filter.Q.value = 1.5;

            this.masterCompression = this.ctx.createDynamicsCompressor();
            this.masterCompression.threshold.value = -10;

            return this.ctx;
        },
        play: function (tune, index) {
            let ctx = null;
            if (this.ctx) {
                ctx = this.ctx;
            } else {
                ctx = this.createCtx();
            }

            let markerStart = Meths.findMarker(index, "start") / window.innerWidth;
            let markerEnd = Meths.findMarker(index, "end") / window.innerWidth;

            if (this.playFrom[index] !== markerStart || this.playTo[index] !== markerEnd) {
                this.playable = false;
            } else {
                this.playable = true;
            }

            // console.log(!this.playing);
            // console.log(this.ableToPlay);
            // console.log(this.playable);
            // console.log(!this.dlding);

            if (!this.playing && this.ableToPlay && this.playable && !this.dlding) {

                this.$emit('able', false);

                this.ableToPlay = false;
                this.convolver.disconnect();

                if (!this.src[index]) {
                    this.getSource(ctx, tune, index);
                } else {
                    this.connectAndPlay(ctx, index);
                }
            }

            this.playFrom[index] = markerStart;
            this.playTo[index] = markerEnd;
        },

        getSource: function (ctx, tune, index) {
            //source and impulse
            const subdir = this.para !== "-" ? this.para + "/" + tune : "" + tune;

            const sourceUrl = this.bucket + "/public/" + subdir;

            document.getElementById(`name_loading_${index}`).innerText = 'Loading...';

            const request = new XMLHttpRequest();
            request.open("GET", sourceUrl, true);
            request.responseType = "arraybuffer";

            const isso = this;
            request.onload = function () {
                const audioData = request.response;

                ctx.decodeAudioData(
                    audioData,
                    function (buffer) {

                        const canvas = document.getElementById("canvas-" + index);
                        canvas.width = window.innerWidth;
                        isso.drawBuffer(canvas.width, canvas.height, canvas.getContext('2d'), buffer);

                        isso.myBuffer[index] = buffer;

                        isso.connectAndPlay(ctx, index);
                    },

                    function (e) {
                        "Error with decoding audio data" + e.err;
                    }
                );
            };

            request.send();

            if (this.first) {
                this.getImpulse();
                this.first = false;
            }
        },

        connectAndPlay: function (ctx, index) {
            let isso = this;

            this.src[index] = ctx.createBufferSource();

            this.src[index].onended = function () {
                if (!isso.stopClicked) {
                    Layout.stopped(index);
                    isso.$emit('ended', index);
                    isso.playing = false;
                    isso.ableToPlay = true;
                }
            }

            this.src[index].buffer = this.myBuffer[index];
            this.src[index].loop = !this.playlist;
            this.gain.gain.value = 0.5;

            this.src[index].connect(this.convolverGain);
            this.src[index].connect(this.gain);
            this.gain.connect(this.filter).connect(this.notch).connect(this.masterCompression);
            this.masterCompression.connect(this.ctx.destination);

            const duration = this.src[index].buffer.duration;
            const offset = duration * this.playFrom[index];
            const endset = duration * this.playTo[index];

            try {
                Layout.playing(index);

                this.src[index].start(0, offset);

                document.getElementById(`name_loading_${index}`).innerText = this.tunesFormatted[index];

                this.playing = true;
                this.loaded = true;

                this.src[index].loopStart = offset;
                this.src[index].loopEnd = endset;

            } catch (err) {
                this.$emit('able', true);
                console.error(err);
            }

            this.fx(index);
        },

        getImpulse: function () {
            const impulseConvolver = this.ctx.createConvolver();
            const impulseRequest = new XMLHttpRequest();

            const impulseUrl = this.public + "/tusk.wav";

            impulseRequest.open("GET", impulseUrl, true);
            impulseRequest.responseType = "arraybuffer";

            const isso = this;
            impulseRequest.onload = function () {
                isso.loaded = true;
                const impulseData = impulseRequest.response;

                isso.ctx.decodeAudioData(
                    impulseData,
                    function (buffer) {
                        const myImpulseBuffer = buffer;
                        impulseConvolver.buffer = myImpulseBuffer;
                        impulseConvolver.loop = true;
                        impulseConvolver.normalize = true;
                        isso.convolverGain.gain.value = 0;
                        isso.convolverGain.connect(impulseConvolver);
                        impulseConvolver.connect(isso.gain);
                    },

                    function (e) {
                        "Error with decoding audio data" + e.err;
                    }
                );
            };

            impulseRequest.send();
        },

        fx: function (index) {
            const isso = this;

            const speedControl = document.getElementsByClassName("speed-control")[0];
            const reverbControl = document.getElementsByClassName("reverb-control")[0];
            const filterControl = document.getElementsByClassName("filter-control")[0];
            const modControl = document.getElementsByClassName("mod-control")[0];

            const speedValue = document.getElementsByClassName("speed-value")[0];
            const reverbValue = document.getElementsByClassName("reverb-value")[0];
            const filterValue = document.getElementsByClassName("filter-value")[0];
            const modValue = document.getElementsByClassName("mod-value")[0];

            const fxInterval = setInterval(function () {

                //try catcher
                try {
                    isso.src[index].playbackRate.value = speedControl.value;
                    speedValue.innerHTML = Math.floor(speedControl.value * 100) + "%";

                    isso.convolverGain.gain.value = reverbControl.value;

                    reverbValue.innerHTML = Math.floor(reverbControl.value * 100) + "%";

                    isso.filter.frequency.value = filterControl.value < 20000 ? filterControl.value / 4 : filterControl.value;

                    filterValue.innerHTML = Math.floor(isso.filter.frequency.value) + " Hz";

                    isso.amt = modControl.value;
                    modValue.innerHTML = Math.floor(modControl.value * 100) + "%";

                } catch (err) {
                    console.error(err);
                }
            }, 50);

            var i = 2;
            var up = true;
            isso.amt = 0;

            var modInt = setInterval(function () {
                if (i === 120) {
                    up = false;
                }
                if (i === 2) {
                    up = true;
                }

                if (up) {
                    i++;
                } else {
                    i--;
                }

                try {
                    if ((i * 100) - isso.amt > 50) {
                        var calc = (-isso.amt * 20000) + 20000;
                        var calcfull = (i * 100) + calc;
                        isso.notch.frequency.value = calcfull < 22000 ? calcfull : 22000;
                    } else {
                        isso.notch.frequency.value = 50;
                    }
                } catch {
                    //
                }

            }, 10);

            const stop = document.getElementById("stbutton-" + index);
            stop.onclick = function () {
                clearInterval(fxInterval);
                clearInterval(modInt);
                isso.stopProcess(index);
            }

            const close = document.getElementById("modal-close-" + index);
            close.onclick = function () {
                clearInterval(fxInterval);
                clearInterval(modInt);
                isso.stopProcess(index);
            }
        },

        isLoading(index) {
            return this.loading[index];
        },

        canvasWidth: function () {
            this.screenWidth = window.innerWidth;
        },

        stopProcess: function (index) {
            var isso = this;
            this.stopClicked = true;
            setTimeout(function () {
                isso.stopClicked = false;
            }, 200);

            Layout.stopped(index);
            this.$emit('able', true);
            this.ableToPlay = true;
            this.src[index].stop(0);
            this.convolver.disconnect();
            this.playing = false;
            this.loaded = false;
        },

        nameTrim: function () {
            const scale = window.innerWidth / 15.16;

            //loop through names

            //.name-element

            // this.nameTrimmed = this.name.length > scale - 7 ? this.name.substr(0, scale - 7) + "..." : this.name;
        },

        download: function () {
            var isso = this;

            isso.dlding = true;
            setTimeout(function () {
                isso.dlding = false;
            }, 500);
        },

        deleteSong: function () {
            var request = new XMLHttpRequest();
            var path = Meths.deleteSongPath(this.para, this.name);

            request.open('GET', path, true);
            request.send();
            this.deleted = true;
        },

        drawBuffer: function (width, height, context, buffer) {
            var data = buffer.getChannelData(0);
            var step = Math.ceil(data.length / width);
            var amp = height / 2;
            for (var i = 0; i < width; i++) {
                var min = 1.0;
                var max = -1.0;
                for (var j = 0; j < step; j++) {
                    var datum = data[(i * step) + j];
                    if (datum < min)
                        min = datum;
                    if (datum > max)
                        max = datum;
                }
                context.fillRect(i, (1 + min) * amp, 1, Math.max(1, (max - min) * amp));
            }
        },

        endHandler(val) {
            var isso = this;
            if (this.playlist) {
                isso.playable = true;

                // check if not last tune
                this.run = val + 1;
            }
        }
    },
    watch: {
        run: function (val) {
            if (val == this.pos) {
                this.play();
            }
        },
    }
}

</script>
<style>

.o-page {
    display: inline !important;
    color: #B27FFF;
    text-decoration: underline;
}

.delete-button {
    cursor: pointer;
}

.false-shift {
    margin-top: 10px;
    /* margin-left: 200px; */
}

.playback {
    clear: left;
}

.playback-item {
    display: inline;
    font-size: 18px;
}

html, body {
    height: 100%;
    overflow-x: hidden;
    /* touch-action: none; */
    background-color: rgb(50, 2, 95) !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-weight: 500 !important;
    /* Disables pull-to-refresh but allows overscroll glow effects. */
    overscroll-behavior-y: contain;
}
body {
    width: 100%;
    position: relative;
    color: #B27FFF !important;
}

.navbar {
    height: 20px;
}

.info-icon {
    float: right;
    font-size: 24px;
    padding-right: 1%;
}

.stack-del button {
    display: none;
}

.upl {
    margin-left: 10px;
    width: 200px;
    float: left;
}

.dl-icon {
    filter: brightness(85%);
    position: absolute;
    right: 0;
    width: 40px;
    top: 0px;
}

.dld {
    background-color: rgba(0,0,0,0);
    border: none;
    position: absolute;
    right: 0;
}

.stack-house {
    width: 100%;
    margin-bottom: 10px;
    cursor: pointer;
    background-color: rgb(110, 78, 158);
}

.stack-slice:hover {
    cursor: pointer;
    filter: brightness(110%);
}

.contr {
    height: 50px;
}

.modal-close {
    cursor: pointer;
    font-size: 24px;
    position: absolute;
    right: 0;
}

.control-box {
    z-index: 10;
    display: none;
    overflow: hidden;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@media (max-width: 600px) {
    .control-box {
        width: 100%;
        transform: translate(-50%, 0);
        bottom: 48px;
        top: initial;
    }
}

@media (min-width: 601px) {
    .control-box {
        width: 400px;
    }
}

.bottom-container {
    background-color: #111;
}

.close-container i {
    display: none;
}

.crow button {
    display: none;
    width: 100%;
}

.ctop-row {
    display: flex;
    height: 300px;
}

.cbottom-row {
    padding: 5px;
    display: flex;
}

.stack-bottom {
    height: 40px;
    background-color: rgb(79, 56, 114);
}

.fx-container {
    flex: 1;
    padding-left: 13.5%;
    padding-top: 270px;
}

.inln-btn {
    z-index: 1;
    position: absolute;
}

.canv {
    position: absolute;
    width: 100%;
    height: 40px;
}

.fx {
    width: 280px;
    transform: rotate(-90deg);
    transform-origin: 0%;
    position: absolute;
}

.cbottom-row span {
    text-align: center;
    flex: 1;
}

.stbutton {
    width: 100%;
}

.cstop button{
    border: none;
    height: 40px;
    background-color: #fff;
}
/* sliders */

/* Hides the slider so that custom slider can be made */
/* Otherwise white in Chrome */

input[type=range] {
    -webkit-appearance: none;
    background: transparent;
}

input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
}

input[type=range]:focus {
    outline: none; /* Removes the blue border. You should probably do some kind of focus styling for accessibility reasons though. */
}

input[type=range]::-ms-track {
    width: 100%;
    cursor: pointer;

    /* Hides the slider so custom styles can be added */
    background: transparent;
    border-color: transparent;
    color: transparent;
}

/* thumb */
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 70px;
    width: 70px;
    border-radius: 50%;
    background: #ffffff;
    /* background: rgb(110, 78, 158); */
    cursor: pointer;
    margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
    box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

}

/* All the same stuff for Firefox */
input[type=range]::-moz-range-thumb {

    box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

    height: 70px;
    width: 70px;
    border-radius: 50%;
    background: #ffffff;
    /* background: rgb(110, 78, 158); */
    cursor: pointer;
}

/* All the same stuff for IE */
input[type=range]::-ms-thumb {
    box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

    height: 70px;
    width: 70px;
    border-radius: 50%;
    background: #ffffff;
    cursor: pointer;
}

/* slider */
/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

/* The slider */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #1aa7b5;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.switcher {
    margin-left: 10px;
}

.pr {
    margin-top: 0.4rem;
}

.slither-class {
    width: 100%;
    top: calc(100% - 40px);
}

</style>
