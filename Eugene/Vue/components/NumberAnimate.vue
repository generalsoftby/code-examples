<script lang="ts">
    import Component from 'vue-class-component';
    import Vue from 'vue';
    import { Prop, Watch } from 'vue-property-decorator';

    import { CountUp, CountUpOptions } from 'countup.js';

    @Component
    export default class NumberAnimate extends Vue {
        @Prop({ required: true, type: Number, default: 0 })
        value: number;

        @Prop({ required: false, type: Object })
        options: CountUpOptions;

        @Prop({ required: false, type: Boolean, default: true })
        runAnimation: boolean;

        counter: CountUp;

        $refs: {
            span: HTMLElement;
        };

        @Watch('value')
        valueChange(newValue: number): void {
            this.createNewCounter(this.counter.frameVal, newValue);
            if (this.runAnimation) {
                this.counter.start();
            }
        }

        @Watch('runAnimation')
        changeAnimationState(neededToBeRun: boolean): void {
            if (this.counter && this.counter.paused && neededToBeRun) {
                this.createNewCounter(this.value);
            }
        }

        mounted(): void {
            this.createNewCounter(this.value, this.value);

            if (this.runAnimation) {
                this.counter.start();
            }
        }

        createNewCounter(startVal?: number, endVal?: number): void {
            if (this.counter && !this.counter.paused) {
                this.counter.pauseResume();
            }

            const options: CountUpOptions = {
                startVal,
                separator: '.',
                ...this.options,
            };
            this.counter = new CountUp(this.$refs.span, endVal, options);
        }
    }
</script>

<template>
    <span ref="span">
        {{ value }}
    </span>
</template>

<style scoped>

</style>
