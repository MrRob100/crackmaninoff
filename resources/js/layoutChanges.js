export default {

    playing(pos) {

      var body = document.querySelector("body");

      this.blur(pos);

        body.style.position = "fixed";
        body.style.overflowY = "hidden";

        document.getElementsByClassName("control-box")[0].style.display = "block";
        document.getElementById("stbutton-" + pos).style.display = "block";
        document.getElementById("modal-close-" + pos).style.display = "block";
    },

    stopped(pos) {
        var box = document.getElementsByClassName("control-box")[0];
        var stop = document.getElementById("stbutton-" + pos);
        var close = document.getElementById("modal-close-" + pos);
        var body = document.querySelector("body");

        body.style.position = "relative";
        body.style.overflowY = "scroll";

        this.unblur(pos);

        stop.style.display = "none";
        close.style.display = "none";
        box.style.display = "none";
    },

    blur(pos) {
        const toBlur = document.getElementsByClassName("to-blur");
        // var noBlur = document.getElementsByClassName("no-blur-" + pos);

        for (let item of toBlur) {
            if (!item.classList.contains("no-blur-" + pos)) {
                item.style.filter = "blur(5px)";
                item.style.cursor = "initial";
                item.style.display = "none";
            }
        }

        const move = document.getElementById("slither-" + pos);
        move.style.position = "absolute";
    },

    unblur(pos) {
        const toBlur = document.getElementsByClassName("to-blur");
        for (let item of toBlur) {
            item.style.filter = "none";
            item.style.cursor = "pointer";
            item.style.display = "block";
        }

        var move = document.getElementById("slither-" + pos);
        move.style.position = "relative";
    }
}
