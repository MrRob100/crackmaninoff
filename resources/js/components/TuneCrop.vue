<template>
  <div id="bar" class="container slider-house">
    <div class="marker marker-start" :id='"div-start-"+setting' :style="'left: '+ start + '%'">
      <div class="markerheader" :id='"div-start-"+setting+"-header"'>S</div>
    </div>
    <div class="marker marker-end" :id='"div-end-"+setting' :style="'left: '+ end">
      <div class="markerheader" :id='"div-end-"+setting+"-header"'>E</div>
    </div>
  </div>
</template>
<script>

import Meths from '../meths.js';

export default {

  props: ['setting', 'name'],

  data: function() {
    return {
      startString: "startScale",
      endString: "endScale",
      start: 0,
      end: "calc(100% -  20px)",
      settingEnd: false
    }
  },

  //when component has mounted
  mounted() {

    var isso = this;

    isso.getMarker("startScale");
    isso.getMarker("endScale");

    var nonHeadStart = document.getElementById("div-start-" + isso.setting);
    var nonHeadEnd = document.getElementById("div-end-" + isso.setting);

    // Make the DIV element draggable:
    dragElement(nonHeadStart);
    dragElement(nonHeadEnd);

    function dragElement(elmnt) {
      var pos1 = 0,
        pos2 = 0,
        pos3 = 0,
        pos4 = 0;
      if (document.getElementById(elmnt.id + "-header")) {
        // if present, the header is where you move the DIV from:
        var mHead = document.getElementById(elmnt.id + "-header");
        mHead.onmousedown = dragMouseDown;
        mHead.ontouchstart = dragMouseDown;
      } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        elmnt.onmousedown = dragMouseDown;
      }

      //first touch
      function dragMouseDown(e) {

        e = e || window.event;
        if (e.touches) {
          pos3 = e.touches[0].clientX;
          document.ontouchend = closeDragElement;
          document.ontouchmove = elementDrag;
        } else {
          e.preventDefault();
          pos3 = e.clientX;
          document.onmouseup = closeDragElement;
          document.onmousemove = elementDrag;
          document.ontouchend = closeDragElement;
          document.ontouchmove = elementDrag;
        }
      }

      function elementDrag(e) {
        e = e || window.event;

        if (e.touches) {
          pos1 = pos3 - e.touches[0].clientX;
          pos3 = e.touches[0].clientX;
        } else {
          e.preventDefault();
          pos1 = pos3 - e.clientX;
          pos3 = e.clientX;
        }
        var startx = nonHeadStart.offsetLeft;
        var endx = nonHeadEnd.offsetLeft;

        if (startx < 0 && elmnt.id == "div-start-" + isso.setting) {
            elmnt.style.left = "0px";
        }

        if (endx > (window.innerWidth - 20) && elmnt.id == "div-end-" + isso.setting) {
            elmnt.style.left = "calc(100% -  20px)";

            if (!isso.settingEnd) {
              isso.setMarkers('endScale', endx);
              isso.settingEnd = true;

              setTimeout(function() {
                isso.settingEnd = false;
              }, 2000);
            }

        }

        if (endx >= startx) {
          var posCalced = ((elmnt.offsetLeft - pos1) / window.innerWidth) * 100;
          elmnt.style.left = elmnt.offsetLeft - pos1
        } else {
          if (elmnt.id == "div-end-" + isso.setting) {
            //end
            elmnt.style.left = elmnt.offsetLeft + 1 + "px";
          }
          if (elmnt.id == "div-start-" + isso.setting) {
            //start
            elmnt.style.left = elmnt.offsetLeft - 1 + "px";
          }
        }
      }

      function closeDragElement(e) {

        //telling parent that drag is starting
        isso.$emit('clicked');

        var addPrevent = document.getElementsByClassName('container')[0];
        addPrevent.setAttribute('id', 'prevent-' + isso.setting);

        setTimeout(function() {
          addPrevent.removeAttribute('id');
        }, 500);

        elmnt.style.left = ((elmnt.offsetLeft - pos1) / window.innerWidth) * 100 + "%";

        if (e.toElement) {
          if (e.toElement.id == "div-start-" + isso.setting + "-header") {
            isso.setMarkers('startScale', e.clientX);
          }

          if (e.toElement.id == "div-end-" + isso.setting + "-header") {
            isso.setMarkers('endScale', e.clientX);
          }
        } else {
          if (e.target.id == "div-start-" + isso.setting + "-header") {
            isso.setMarkers('startScale', e.pageX);
          }

          if (e.target.id == "div-end-" + isso.setting + "-header") {
            isso.setMarkers('endScale', e.pageX);
          }
        }

        if (pos3 < 10) {
            isso.setMarkers('startScale', 0);
        }

        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
        document.ontouchend = null;
        document.ontouchmove = null;
      }
    }
  },
  methods: {

    //return numeric
    getMarker: function(which) {

      var isso = this;

      var request = new XMLHttpRequest();
      var path = Meths.getMarkersPath(this.para, this.name);

      request.open('GET', path, true);
      request.send();
      request.onload = function() {

        if (which === "startScale") {

          // get start point from db for song id
          var startPoint = 0.1;

          isso.start = startPoint * 100;
          isso.$emit('setStart', which, startPoint);
        }

        if (which === "endScale") {


            //get  end point from db for song id
            var endPoint = 0.9;

          if (request.response === "") {
            endPoint = 0.98;
            isso.end = "calc(100% - 20px)";
          } else {
            isso.end = endPoint * 100 + "%";

          }
          isso.$emit('setEnd', which, endPoint);
        }
      }

    },

    setMarkers: function(which, value) {
      var scaledValue = value / window.innerWidth;
      var request = new XMLHttpRequest();
      var path = Meths.setMarkersPath(this.para, this.name, which, scaledValue);
      request.open('GET', path, true);
      request.send();
      this.$emit('value', which, scaledValue);
      }
  }
};
</script>
<style>
.slider-house {
  background-color: aqua;
  padding-left: 0;
}

.marker {
  width:20px;
  height: 40px;
  position: absolute;
  z-index: 9;
  /* background-color: #f1f1f1; */
  border: 2px solid #d3d3d3;
  text-align: center;
}


.marker-start {
  border-right: none;
}

.marker-end {
  border-left: none;
}

.markerheader {
  color: #d3d3d3;
  height: 40px;
  cursor: move;
  z-index: 10;
}

</style>
