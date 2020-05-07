<template>
    <div v-if="(settings.bend.sizes.length > 0) && settings.bend.use">
        <radio :name="radioInputName" @input="$emit('input', [])" :checked="!value.length">
            {{ i18n.sections_choosing_whole }}
        </radio>
        <radio :name="radioInputName" @input="$emit('input', [0])" :checked="!!value.length">
            {{ i18n.sections_choosing_concrete }}
        </radio>
        <div v-if="value.length" class="placement-parts">
            <div class="parts">
                <div
                        v-for="(sectionSize, sectionIndex) in sectionsSizes"
                        class="part"
                        :data-index="sectionIndex"
                        :class="{
                            active: -1 !== value.indexOf(sectionIndex),
                            'with-separator-before': sectionIndex > 0,
                            'with-separator-after': false,
                        }"
                        :style="{
                            left: ((settings.bend.type === 'vertical') ? (sectionSize.left / size.w) : (sectionSize.top / size.h)) * 100 + '%',
                            width: ((settings.bend.type === 'vertical') ? (sectionSize.width / size.w) : (sectionSize.height / size.h)) * 100 + '%',
                        }"
                        @click="$emit('input', [sectionIndex])"
                        @mouseover.exact="$event.target.classList.add('hover')"
                        @mouseout.exact="$event.target.classList.remove('hover')"
                >
                    {{ sectionIndex + 1 }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex';

    export default {
        props: {value: {type: Array}},
        data: () => ({
            radioInputName: 'section-choosing-' + Math.random().toString(36).substring(7),
        }),
        computed: {
            ...mapState(['i18n']),
            ...mapGetters('current', [
                'settings',
                'size',
                'sectionsSizes',
            ]),
        },
    }
</script>
