export default {

    playing(pos) {

      var body = document.querySelector("body");
      var toBlur = document.getElementsByClassName("to-blur");

        for (let item of toBlur) {
          item.style.filter = "blur(5px)";
          item.style.cursor = "initial";
        }

        body.style.position = "fixed";
        body.style.overflowY = "hidden";

        document.getElementsByClassName("control-box")[0].style.display = "block";
        document.getElementById("stbutton-" + pos).style.display = "block"; 
        document.getElementById("modal-close-" + pos).style.display = "block"; 

    },

    stopped(pos) {
        var toBlur = document.getElementsByClassName("to-blur");
        var box = document.getElementsByClassName("control-box")[0];
        var stop = document.getElementById("stbutton-" + pos);
        var close = document.getElementById("modal-close-" + pos);
        var body = document.querySelector("body");

        body.style.position = "relative";
        body.style.overflowY = "scroll";

        for (let item of toBlur) {
            item.style.filter = "none";
            item.style.cursor = "pointer";
        }

        stop.style.display = "none";
        close.style.display = "none";
        box.style.display = "none";   
    }

}