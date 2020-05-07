<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import { namespace } from 'vuex-class';

    import { contextsHttp } from '@/http';
    import BaseDropdown from '@/common/components/dropdown/BaseDropdown.vue';
    import BaseDropdownMenu from '@/common/components/dropdown/BaseDropdownMenu.vue';
    import BaseDropdownItem from '@/common/components/dropdown/BaseDropdownItem.vue';
    import { Context } from '@/types';
    import InfinityScrollManager from '@/services/InfinityScrollManager';
    import BaseScroll from '@/common/components/BaseScroll.vue';
    import { ListApiResponse } from '@/types/api';

    const { State, Action } = namespace('app');
    const { Action: ListAction } = namespace('contacts/list');

    @Component({
        components: {
            BaseScroll,
            BaseDropdownItem,
            BaseDropdownMenu,
            BaseDropdown,
        },
    })
    /**
     * @group App
     * This component renders dropdown for context change.
     * We integrated vuex `app` for managing current application context
     * and `contacts/list` for cleanups after context change.
     */
    export default class AppContextSwitcher extends Vue {
        @State
        context: Context;

        @Action
        updateCurrentContext: (context: Context) => Promise<Context>;

        @ListAction
        clearStorage: () => void;

        scrollManager = new InfinityScrollManager<Context>(this.getContexts);

        /**
         * @vuese
         * Infinity scroll handler.
         * @arg The first parameter is a request filter.
         */
        getContexts(params): Promise<ListApiResponse<Context>> {
            return contextsHttp.getFiltered(params).then(res => res.data);
        }

        /**
         * @vuese
         * Handle selection of context.
         * @arg The first parameter is a context.
         */
        async selectContext(context: Context): Promise<void> {
            if (context.id === this.context.id) {
                return;
            }
            this.clearStorage();

            await this.updateCurrentContext(context);
            this.$router.push('/');
        }

        /**
         * @vuese
         * Handle dropdown visibility and load accessible contexts if needed.
         */
        handleDropDownVisibleChange(visible: boolean): void {
            if (!visible) {
                return;
            }

            this.scrollManager.getItems();
        }

        /**
         * @vuese
         * Infinity scroll handler.
         */
        handleGetMoreContexts(): void {
            this.scrollManager.getMoreItems();
        }
    }
</script>

<template>
    <base-dropdown
        class="app-context-switcher"
        @visible-change="handleDropDownVisibleChange"
        @command="selectContext"
    >
        {{ context && context.name }}
        <base-dropdown-menu
            slot="dropdown"
            class="app-context-switcher__list"
            :style="{ height: scrollManager.items.length > 6 && '239px' }"
            visible-arrow
        >
            <base-scroll
                size="small"
                @scroll-end="handleGetMoreContexts"
            >
                <base-dropdown-item
                    v-for="context in scrollManager.items"
                    :key="context.id"
                    :command="context"
                >
                    {{ context.name }}
                </base-dropdown-item>
            </base-scroll>
        </base-dropdown-menu>
    </base-dropdown>
</template>

<style lang="scss">
    .app-context-switcher {
        $height: $middle-size;

        .base-dropdown__trigger {
            line-height: $height;
            font-size: $font-size-extra-small;
            color: $font-color-secondary;
        }

        &__list {
            width: getSize(32) !important;
        }
    }
</style>
