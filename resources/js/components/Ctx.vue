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
            <tune
                :auth="auth"
                :storage-path="storagePath"
                :img-path="imgPath"
                class="slither-class"
                :id="'slither-' + index"
                @ended="endHandler"
                @able="setPlayable"
                :playable="playable"
                :playlist="playlist"
                :ctx="ctx"
                :para="para"
                :name="tune"
                :pos="index"
                :run="run"
                :lastOne="tunesFormatted.length"
            ></tune>
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

export default {

    props: ["tunes", "para", "img-path", "storage-path", "auth"],

    data: function() {
        return {
            playlist: false,
            init: true,
            initSource: {},
            tunesFormatted: {},
            playable: true,
            run: false,
        }
    },

    mounted() {
        var isso = this;
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const audioCtx = new AudioContext();
        this.ctx = audioCtx;
        this.tunesFormatted = this.tunes.split(" ");

        const loopUrl = this.storagePath + "tenniscourt.wav";

        const source = audioCtx.createBufferSource();

        var request = new XMLHttpRequest();
        request.open('GET', loopUrl, true);
        request.responseType = 'arraybuffer';

        request.onload = function() {
            var audioData = request.response;

            audioCtx.decodeAudioData(audioData, function(buffer) {
                var myBuffer = buffer;
                source.buffer = myBuffer;
                source.connect(audioCtx.destination);
                isso.initSource = source;
            },
            function (e) {
                "Error decoding audio data"
            });
        }

        request.send();
    },

    methods: {
        setPlayable(playable) {
            this.playable = playable;

            if (!playable && this.init) {
                this.init = false;
                this.initSource.start(0, 1);
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
    }
}

</script>
