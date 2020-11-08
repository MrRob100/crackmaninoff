<template>
  <div v-if="!deleted">
    <h3
    @click="deleteSong"
    class="delete-button"
    >DELETE</h3>
    <div class="stack-house to-blur">
      <div
      @click="play"
      :id='"stack-" + pos'
      class="stack-slice stack-bottom"
      >
        <div class="inln-btn">
            <h3 class="false-shift" v-if="!loading">{{ nameTrimmed }}<i class="fa fa-hand-pointer-o"></i></h3>
            <h3 class="false-shift" v-else>Loading...</h3>
        </div>

        <tune-crop
        @value="cropVal"
        @setStart="playSelection"
        @setEnd="playSelection"
        :id='"tc-"+pos'
        :setting='pos'
        :name='name'
        ></tune-crop>
        <canvas class="canv" :id='"canvas-"+pos'></canvas>
        <a :href="dlref"
        >
        <button
        class="dld"
        v-on:click="dl">
        <img data-toggle="tooltip" title="download" class="crack-icon dl-icon" src="images/dld.png">
        </button>
        </a>
      </div>
    </div>
  </div>
</template>

<script>

import Meths from '../meths.js';
import Layout from '../layoutChanges.js';

export default {
  props: [
    "playable",
    "playlist",
    "ctx",
    "para",
    "name",
    "pos",
    "run"
    ],

  data: function() {
    return {
      deleted: false,
      screenWidth: "",
      nameTrimmed: "",
      playFrom: "",
      playTo: "",
      ableToPlay: true,
      first: true,
      dlref: "",
      dlding: false,
      playing: false,
      loading: false,
      loaded: false,
      src: null,
      convolver: {},
      convolverGain: {},
      gain: {},
      filter: {},
      notch: {},
      masterCompression: {},
      amt: 0,
      stopClicked: false
    };
  },

  mounted() {

    var isso = this;
    var body = document.querySelector("body");
    var close = document.getElementById("modal-close-" + isso.pos);
    var stop = document.getElementById("stbutton-" + isso.pos);


    if (this.para == "-") {
      isso.dlref = window.location.origin + "/dl?song=" + isso.name;
    } else {
      isso.dlref = window.location.origin + "/dl?song=" + isso.para + "/" + isso.name;
    }

    var source;
    var myBuffer;
    var myImpulseBuffer;
    var impulseRequest;
    var impulseConvolver = isso.ctx.createConvolver();

    this.convolver = isso.ctx.createConvolver();
    this.convolverGain = isso.ctx.createGain();

    this.gain = isso.ctx.createGain();
    this.filter = isso.ctx.createBiquadFilter();
    this.notch = isso.ctx.createBiquadFilter();

    this.filter.type = "lowpass";
    this.filter.frequency.value = 20000;

    this.notch.type = 'notch';
    this.notch.frequency.value = 100;
    this.filter.Q.value = 1.5;

    this.masterCompression = isso.ctx.createDynamicsCompressor();
    this.masterCompression.threshold.value = -10;

    this.nameTrim();
    this.canvasWidth();
    window.addEventListener("resize", this.nameTrim);
    window.addEventListener("resize", this.canvasWidth);

  },

  methods: {

    play: function() {
      this.$emit('able', false);
      var prevent = document.getElementById('prevent-' + this.pos);
      if (!this.playing && this.ableToPlay && !prevent && this.playable && !this.dlding) {
        this.ableToPlay = false;
        this.convolver.disconnect();

        //if not cached / loaded...

        if (!this.src) {
          this.getSource();
        } else {
          this.connectAndPlay();
        }

      }
    },

    getSource: function() {

      //source and impulse
      var subdir = this.para !== "-" ? this.para + "/" + this.name : "" + this.name;
      var sourceUrl = "storage/data/" + subdir;

      this.loading = true;

      var request = new XMLHttpRequest();
      request.open("GET", sourceUrl, true);
      request.responseType = "arraybuffer";

      var isso = this;
      request.onload = function() {
        var audioData = request.response;

        isso.ctx.decodeAudioData(
          audioData,
          function(buffer) {

            //canvas
            var canvas = document.getElementById("canvas-" + isso.pos);
            canvas.width = window.innerWidth;
            isso.drawBuffer( canvas.width, canvas.height, canvas.getContext('2d'), buffer );

            //audio
            isso.myBuffer = buffer;

            isso.connectAndPlay();

          },

          function(e) {
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

    connectAndPlay: function() {
      var isso = this;

      this.src = this.ctx.createBufferSource();

      this.src.onended = function() {

          console.log('check stop clicked: ', isso.stopClicked);

          if (!isso.stopClicked) {
              Layout.stopped(isso.pos);
              isso.$emit('ended', isso.pos);
          }
      }

      this.src.buffer = this.myBuffer;

      this.src.loop = !this.playlist;

      this.gain.gain.value = 0.5;

      this.src.connect(this.convolverGain);
      this.src.connect(this.gain);
      this.gain.connect(this.filter).connect(this.notch).connect(this.masterCompression);
      this.masterCompression.connect(this.ctx.destination);

      //start from
      var duration = this.src.buffer.duration;
      var offset = duration * this.playFrom;
      var endset = duration * this.playTo;

      try {

        Layout.playing(this.pos);

        this.src.start(0, offset);
        this.loading = false;

        this.playing = true;
        this.loaded = true;

        this.src.loopStart = offset;
        this.src.loopEnd = endset;

      } catch(err) {
        this.$emit('able', true);
        console.error(err);
      }

      this.fx();

    },

    getImpulse: function() {
      var impulseConvolver = this.ctx.createConvolver();
      var impulseRequest = new XMLHttpRequest();
      var impulseUrl = "storage/data/tenniscourt.wav";

      impulseRequest.open("GET", impulseUrl, true);
      impulseRequest.responseType = "arraybuffer";

      var isso = this;
      impulseRequest.onload = function() {
        isso.loaded = true;
        var impulseData = impulseRequest.response;

        isso.ctx.decodeAudioData(
          impulseData,
          function(buffer) {
            var myImpulseBuffer = buffer;
            impulseConvolver.buffer = myImpulseBuffer;
            impulseConvolver.loop = true;
            impulseConvolver.normalize = true;
            isso.convolverGain.gain.value = 0;
            isso.convolverGain.connect(impulseConvolver);
            impulseConvolver.connect(isso.gain);
          },

          function(e) {
            "Error with decoding audio data" + e.err;
          }
        );
      };

      impulseRequest.send();
    },

    fx: function() {
      var isso = this;

      var speedControl = document.getElementsByClassName("speed-control")[0];
      var reverbControl = document.getElementsByClassName("reverb-control")[0];
      var filterControl = document.getElementsByClassName("filter-control")[0];
      var modControl = document.getElementsByClassName("mod-control")[0];

      var speedValue = document.getElementsByClassName("speed-value")[0];
      var reverbValue = document.getElementsByClassName("reverb-value")[0];
      var filterValue = document.getElementsByClassName("filter-value")[0];
      var modValue = document.getElementsByClassName("mod-value")[0];

      var fxInterval = setInterval(function() {

        //try catcher
        try {
          isso.src.playbackRate.value = speedControl.value;
          speedValue.innerHTML = Math.floor(speedControl.value * 100) + "%";

          isso.convolverGain.gain.value = reverbControl.value;
          reverbValue.innerHTML = Math.floor(reverbControl.value * 100) + "%";

          isso.filter.frequency.value = filterControl.value < 20000 ? filterControl.value / 4 : filterControl.value;

          filterValue.innerHTML = Math.floor(isso.filter.frequency.value) + " Hz";

          isso.amt = modControl.value;
          modValue.innerHTML = Math.floor(modControl.value * 100) + "%";

          } catch(err) {
            console.error(err);
        }
      }, 50);

      var i = 2;
      var up = true;
      isso.amt = 0;

      var modInt = setInterval(function() {
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

      var stop = document.getElementById("stbutton-" + isso.pos);
      stop.onclick = function() {
        clearInterval(fxInterval);
        clearInterval(modInt);
        isso.stopProcess();
      }

      var close = document.getElementById("modal-close-" + isso.pos);
      close.onclick = function() {
        clearInterval(fxInterval);
        clearInterval(modInt);
        isso.stopProcess();
      }

    },

    canvasWidth: function() {
      this.screenWidth = window.innerWidth;
    },

    cropVal: function(which, value) {
      var isso = this;
      isso.ableToPlay = false;s
      setTimeout(function() {
          isso.ableToPlay = true;
          isso.$emit('able', true);
      }, 500);
      this.playSelection(which, value);
    },

    stopProcess: function() {
      var isso = this;

      //prevent roll on to next song

      this.stopClicked = true;

      console.log('stop click set: ', this.stopClicked);

      setTimeout(function() {
          isso.stopClicked = false;
      }, 200);

      Layout.stopped(this.pos);
      this.$emit('able', true);
      this.ableToPlay = true;
      this.src.stop(0);
      this.convolver.disconnect();
      this.playing = false;
      this.loaded = false;

    },

    playSelection: function(which, value) {

      if (which === "startScale") {
        this.playFrom = value;
      }
      if (which === "endScale") {
        this.playTo = value > 0.98 ? 1 : value;
      }
    },

    nameTrim: function() {
      var scale = window.innerWidth / 15.16;
      this.nameTrimmed = this.name.length > scale - 7 ? this.name.substr(0, scale - 7) + "..." : this.name;
    },

    dl: function() {

        var isso = this;

        isso.dlding = true;
        setTimeout(function() {
            isso.dlding = false;
        }, 500);
    },

    deleteSong: function() {

      var request = new XMLHttpRequest();

      var path = Meths.deleteSongPath(this.para, this.name);

      request.open('GET', path, true);
      request.send();
      this.deleted = true;
    },

    drawBuffer: function( width, height, context, buffer ) {
      var data = buffer.getChannelData( 0 );
      var step = Math.ceil( data.length / width );
      var amp = height / 2;
      for(var i=0; i < width; i++){
          var min = 1.0;
          var max = -1.0;
          for (var j=0; j<step; j++) {
              var datum = data[(i*step)+j];
              if (datum < min)
                  min = datum;
              if (datum > max)
                  max = datum;
              }
      context.fillRect(i,(1+min)*amp,1,Math.max(1,(max-min)*amp));
      }
    },

  },

    watch: {
      run: function(val) {
          var isso = this;
          if (val == this.pos) {
              this.play();
          }
      }
    }
};
</script>
