<script lang="ts">
    import Component, { mixins } from 'vue-class-component';

    import BaseModalWindowBody from '@/common/components/modal/BaseModalWindowBody.vue';
    import BaseModalWindowHeader from '@/common/components/modal/BaseModalWindowHeader.vue';
    import BaseModalWindow from '@/common/components/modal/BaseModalWindow.vue';
    import BaseFooter from '@/common/components/BaseFooter.vue';
    import Modal from '@/common/mixins/Modal';
    import PaginatedList from '@/common/mixins/PaginatedList';
    import BasePagination from '@/common/components/pagination/BasePagination.vue';
    import BaseRadioGroup from '@/common/components/radio/BaseRadioGroup.vue';
    import BaseRadio from '@/common/components/radio/BaseRadio.vue';

    @Component({
        components: {
            BaseRadio,
            BaseRadioGroup,
            BasePagination,
            BaseFooter,
            BaseModalWindow,
            BaseModalWindowHeader,
            BaseModalWindowBody,
        },
    })
    export default class BaseListModal<T = any> extends mixins(Modal, PaginatedList) {
        title = '';

        selected: T = null;

        perPage = 8;

        isItemDisabled(item: T): boolean {
            return false;
        }

        getItemText(item: T): string {
            return item.toString();
        }

        handleConfirm(): void {
            this.onSuccess(this.selected);
            this.closeModal();
        }

        handleCancel(): void {
            this.closeModal();
        }

        handlePageChange(page: number): void {
            this.changePage(page);
        }
    }
</script>

<template>
    <base-modal-window class="base-list-modal">
        <base-modal-window-header>
            {{ title }}
        </base-modal-window-header>
        <base-modal-window-body>
            <base-radio-group
                    v-model="selected"
                    class="base-list-modal__radio-group"
            >
                <el-col
                        v-for="(item, index) in items"
                        :key="index"
                        :span="12"
                        class="base-list-modal__col"
                >
                    <base-radio
                            :label="item"
                            :disabled="isItemDisabled(item)"
                            class="base-list-modal__radio"
                    >
                        {{ getItemText(item) }}
                    </base-radio>
                </el-col>
            </base-radio-group>
            <base-pagination
                    class="base-list-modal__pagination"
                    :pager-count="5"
                    layout="prev, pager, next"
                    :total="total"
                    :page="page"
                    :page-size="perPage"
                    @page-change="handlePageChange"
            />
        </base-modal-window-body>
        <base-footer
                :confirm-disabled="!selected"
                @confirm="handleConfirm"
                @cancel="handleCancel"
        />
    </base-modal-window>
</template>

<style lang="scss" scoped>
    .base-list-modal {
        &__radio-group {
            width: getSize(50);
            margin-bottom: $middle-size;
        }

        &__radio {
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: getSize(3);
        }

        &__col {
            &:nth-child(odd) {
                padding-right: getSize(1.5);
            }

            &:nth-child(even) {
                padding-left: getSize(1.5);
            }
        }

        &__pagination {
            padding: 0;
        }
    }
</style>
