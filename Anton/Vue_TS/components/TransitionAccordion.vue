<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import Velocity from 'velocity-animate';
    import { Prop } from 'vue-property-decorator';

    type DoneCallBack = () => void;

    @Component
    export default class TransitionAccordion extends Vue {
        @Prop({ type: Number, default: 200 })
        duration: number;

        @Prop({ type: Number, default: 0 })
        delay: number;

        beforeEnter(el: HTMLElement): void {
            el.style.height = '0px';
            el.style.overflow = 'hidden';
        }

        enter(el: HTMLElement, done: DoneCallBack): void {
            Velocity(el, {
                overflow: 'hidden',
                height: el.scrollHeight,
            }, {
                duration: this.duration,
                delay: this.delay,
                complete: done,
            });
        }

        leave(el: HTMLElement, done: DoneCallBack): void {
            Velocity(el, {
                height: 0,
            }, {
                duration: this.duration,
                delay: this.delay,
                complete: done,
            });
        }
    }
</script>

<template>
    <transition
            :css="false"
            @before-enter="beforeEnter"
            @enter="enter"
            @leave="leave"
    >
        <slot />
    </transition>
</template>

<style lang="scss" scoped>

</style>
