<template>
    <svg
            style="position: absolute; left: 0; top: 0; width: 100%; height: 100%;"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            :viewBox="'0 0 ' + width + ' ' + height"
    >
        <defs>
            <pattern
                    :id="'svgTilingPattern-' + objectId"
                    patternUnits="userSpaceOnUse"
                    :x="patternOffset.x"
                    :y="patternOffset.y"
                    :width="tileWidth + tilePadding"
                    :height="tileHeight + tilePadding"
                    :patternTransform="'rotate(' + tiling.rotate + ', ' + (width / 2) + ', ' + (height / 2) + ')'"
            >
                <tiling-svg-tile
                        :image-width="imageWidth"
                        :image-height="imageHeight"
                        :tile-width="tileWidth"
                        :tile-height="tileHeight"
                        :image-url="imageUrl"
                        :image-content="imageContent"
                ><slot></slot></tiling-svg-tile>
            </pattern>
        </defs>
        <rect width="100%" height="100%" :fill="'url(#svgTilingPattern-' + objectId + ')'"/>
    </svg>
</template>

<script>
    import TilingSvgTile from './TilingSvgTile';

    export default {
        components: {TilingSvgTile},
        props: [
            'objectId',
            'width',
            'height',
            'imageUrl',
            'imageContent',
            'imageWidth',
            'imageHeight',
            'tiling',
        ],
        computed: {
            tileWidth() {
                const ratio = (this.width / this.height) / (this.imageWidth / this.imageHeight);
                return ((ratio < 1) ? 1 : (1 / ratio)) * this.width * this.tiling.scale;
            },
            tileHeight() {
                const ratio = (this.width / this.height) / (this.imageWidth / this.imageHeight);
                return ((ratio < 1) ? ratio : 1) * this.height * this.tiling.scale;
            },
            tilePadding() {
                return Math.min(this.tileWidth, this.tileHeight) * this.tiling.padding / 100;
            },
            patternOffset() {
                return {
                    x: (this.width - this.tileWidth) / 2 + this.tiling.x * this.tiling.scale,
                    y: (this.height - this.tileHeight) / 2 + this.tiling.y * this.tiling.scale,
                };
            },
            gridSideTilesCount() {
                return (Math.max(...[
                    Math.ceil((this.width + this.tilePadding) / (this.tileWidth + this.tilePadding)),
                    Math.ceil((this.width + this.tilePadding) / (this.tileHeight + this.tilePadding)),
                ]) + 1) | 1;
            },
            gridWidth() {
                return this.gridSideTilesCount * (this.tileWidth + this.tilePadding) - this.tilePadding;
            },
            gridHeight() {
                return this.gridSideTilesCount * (this.tileHeight + this.tilePadding) - this.tilePadding;
            },
        },
    }
</script>
