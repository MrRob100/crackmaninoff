<template>
    <div class="container">
        <div class="fx-container speed-container">
            <input class="fx speed-control" type="range" min="0" max="1" step="0.01" value="1">
        </div>
    </div>
</template>

<script>
    export default {
        props: ['prop1', 'prop2'],

        data: function() {
            return {
                data1: {},
                data2: {}
            }
        },

        mounted() {
            console.log('Component mounted.')
        },

        computed: {
            //
        },

        watch: {
            // whenever question changes, this function will run
            playing: function (newState, oldState) {
            // console.log('was', oldState, 'now', newState);
            }
        },

        methods: {
            //       
        }
    }
</script>
<style>

</style>

