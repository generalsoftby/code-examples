<template>
    <svg
            xmlns="http://www.w3.org/2000/svg"
            version="1.1"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            :viewBox="svgViewBox"
            :style="svgStyle"
    >
        <defs>
            <path :id="'svgTextPath-' + object.id" :d="svgTextPath"></path>
        </defs>
        <text
                :font-size="object.font.size + 'pt'"
                :font-family="object.font.family"
                :font-style="object.font.style"
                :font-weight="object.font.weight"
                :fill="textColor"
                dominant-baseline="middle"
        >
            <textPath :xlink:href="'#svgTextPath-' + object.id">
                {{ textValue }}
            </textPath>
        </text>
    </svg>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        props: ['object', 'textValue', 'textColor'],
        computed: {
            ...mapState('ui/canvas', {canvasLayout: 'component'}),
            svgTextPath() {
                const object = this.object;
                const length = this.object.textWidth * 1.01;
                const diameter = length / Math.PI;
                const angle = Math.min(Math.abs(object.rounding.angle), 359.999);
                const alpha = angle % 180 / 180;
                const extra = angle > 180;
                const px = (extra ? 1 : alpha) * (length / 2 - diameter) + (extra ? (alpha * diameter) : 0);
                const py = (length / 2) * (extra ? (1 + (1 / 2.5 - alpha / 10) * Math.sign(object.rounding.angle)) : (1 + (alpha / 2.5) * Math.sign(object.rounding.angle)));
                const radius = length / ((1 + alpha) * Math.PI);
                const rx = extra ? radius : diameter;
                const ry = extra ? rx : (alpha * rx);
                return 'M ' + (px + object.textHeight / 2) + ' ' + (py + object.textHeight / 2) +
                    ' A ' + rx + ' ' + ry +
                    ' 0 1 ' + ((object.rounding.angle >= 0) ? 1 : 0) +
                    ' ' + (length - px + object.textHeight / 2) + ' ' + (py + object.textHeight / 2) + '';
            },
            svgViewBox() {
                const object = this.object;
                return (
                    ((object.textWidth + object.textHeight - object.width) / 2) + ' ' +
                    ((object.textWidth + object.textHeight - object.height) / 2) + ' ' +
                    (object.width) + ' ' +
                    (object.height)
                );
            },
            svgStyle() {
                const object = this.object;
                return {
                    position: 'absolute',
                    left: -(object.textWidth + object.textHeight - object.width) / 2 + 'px',
                    top: -(object.textWidth + object.textHeight - object.height) / 2 + 'px',
                    width: (object.textWidth + object.textHeight) + 'px',
                    height: (object.textWidth + object.textHeight) + 'px',
                };
            },
        },
    }
</script>
