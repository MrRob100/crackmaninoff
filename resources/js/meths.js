export default {
    //returns path
    //deals with env and possible subdirs
    getSong(para, name) {
        var subdir = para !== "-" ? para + "/" + name : "" + name;
        return 'storage/data/' + subdir;
    },

    deleteSongPath(para, name) {
        return 'del?song=' + name + '&para=' + para;
    },

    //which: startScale or endScale
    setMarkersPath(para, name, which, value) {
        return 'set?which=' + which + "&position=" + name + "&value=" + value;
    },

    //which: startScale or endScale
    getMarkersPath(para, name, which) {
        return 'get?which=' + which + "&position=" + name;
    },

    //check markers position
    findMarker(pos, which) {
        var marker = document.getElementById('div-' + which + '-' + pos);
        return marker.offsetLeft;
    }

}
