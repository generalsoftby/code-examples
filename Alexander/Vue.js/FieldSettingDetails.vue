<script lang="ts">
    import Component, { mixins } from 'vue-class-component';
    import { namespace } from 'vuex-class';

    import BaseDrawer from '@/common/components/drawer/BaseDrawer.vue';
    import BaseDrawerBody from '@/common/components/drawer/BaseDrawerBody.vue';
    import BaseDrawerHeader from '@/common/components/drawer/BaseDrawerHeader.vue';
    import { FieldSetting, PartialLabelSetting } from '@/types';
    import FieldSettingDetailsSelect from '@/components/fieldSettings/details/FieldSettingDetailsSelect.vue';
    import FieldSettingDetailsPreview from '@/components/fieldSettings/details/FieldSettingDetailsPreview.vue';
    import { AbstractOptions } from '@/types/fieldSetting/options';
    import FieldSettingDetailsCurrency from '@/components/fieldSettings/details/FieldSettingDetailsCurrency.vue';
    import FieldSettingsDetailsTypeSelect from '@/components/fieldSettings/details/FieldSettingsDetailsTypeSelect.vue';
    import FieldSettingDetailsMultiValues from '@/components/fieldSettings/details/FieldSettingDetailsMultiValues.vue';
    import FieldSettingDetailsCalendar from '@/components/fieldSettings/details/FieldSettingDetailsCalendar.vue';
    import { isEqual } from 'lodash';
    import BaseScroll from '@/common/components/BaseScroll.vue';
    import BaseFooter from '@/common/components/BaseFooter.vue';
    import { notifySuccess } from '@/utils/notificationsHelper';
    import fieldTypes from '@/utils/enums/fieldTypes';
    import { createFieldSetting } from '@/services/objectFactory';
    import { fieldSettingsHttp } from '@/http';
    import DebounceValidation from '@/common/mixins/DebounceValidation';
    import RoutePermissionCheck from '@/common/mixins/RoutePermissionCheck';

    const {
        State,
        Action,
        Mutation,
        Getter,
    } = namespace('fieldSettings/details');

    const NEW_ID = 'new';

    @Component({
        components: {
            BaseFooter,
            BaseScroll,
            FieldSettingDetailsMultiValues,
            FieldSettingsDetailsTypeSelect,
            FieldSettingDetailsPreview,
            BaseDrawerHeader,
            BaseDrawerBody,
            BaseDrawer,
        },
    })
    // @group FieldSettings
    export default class FieldSettingDetails extends mixins(DebounceValidation, RoutePermissionCheck) {
        types = {
            [fieldTypes.textInput]: '',
            [fieldTypes.email]: '',
            [fieldTypes.textarea]: '',
            [fieldTypes.numberInput]: '',
            [fieldTypes.currency]: FieldSettingDetailsCurrency,
            [fieldTypes.bankAccount]: '',
            [fieldTypes.select]: FieldSettingDetailsSelect,
            [fieldTypes.multiSelect]: FieldSettingDetailsSelect,
            [fieldTypes.calendar]: FieldSettingDetailsCalendar,
            [fieldTypes.sourceId]: '',
            [fieldTypes.address]: '',
        };

        disabled = true;

        @State
        setting: FieldSetting;

        @Getter
        isNewSetting: boolean;

        @Action
        getSetting: (id: string) => Promise<FieldSetting>;

        @Mutation
        setSetting: (setting: FieldSetting) => void;

        @Mutation
        setUsages: (usages: PartialLabelSetting[]) => void;

        @Mutation
        setPreviewValue: (value: any) => void;

        @Action
        updateSetting: () => Promise<FieldSetting>;

        @Action
        createSetting: () => Promise<FieldSetting>;

        @Getter('getName')
        translatedName: string;

        @Mutation
        setOriginalState: (value: FieldSetting) => void;

        @State
        originalState: FieldSetting;

        @Getter
        canEdit: boolean;

        get name(): string {
            return this.translatedName;
        }

        set name(name: string) {
            this.setSetting({ ...this.setting, name });

            if (name) {
                this.debouncedValidation();
            }
        }

        get options(): AbstractOptions {
            return this.setting ? this.setting.options : { type: '' };
        }

        get isReady(): boolean {
            return this.setting && !this.isNewSetting
                ? this.setting.id === this.$route.params.fieldSettingId
                : this.isNewSetting
            ;
        }

        get isValid(): boolean {
            return this.valid && !!this.name && !isEqual(this.setting, this.originalState);
        }

        get id(): string {
            return this.$route.params.fieldSettingId;
        }

        async created(): Promise<void> {
            if (this.id === NEW_ID) {
                const newSetting = createFieldSetting();
                this.setSetting(newSetting);
            } else if (!this.isReady) {
                await this.getSetting(this.id);
            }

            this.setOriginalState(this.setting);
            this.disabled = !this.canEdit;
        }

        destroyed(): void {
            this.setSetting(null);
            this.setUsages(null);
        }

        validate(): Promise<boolean> {
            return fieldSettingsHttp.checkSetting(this.setting).then(res => res.data);
        }

        handleUpdateOptions(options: AbstractOptions): void {
            this.setPreviewValue({});
            this.setSetting({ ...this.setting, options });
        }

        async handleSave(): Promise<void> {
            if (!this.isNewSetting) {
                await this.updateSetting();
                this.$emit('field-update', this.setting);
                notifySuccess(this.$tc('common.notifications.change'));
            } else {
                await this.createSetting();
                this.$emit('field-create', this.setting);
                notifySuccess(this.$tc('common.notifications.create.field'));
            }
            this.closeDrawer();
        }

        async handleCancel(): Promise<void> {
            if (isEqual(this.originalState, this.setting)) {
                this.closeDrawer();

                return;
            }

            const buttons = {
                confirmText: this.$t('common.yes').toString(),
                cancelText: this.$t('common.no').toString(),
            };

            try {
                await this.$confirmation(this.$tc('common.messages.leaveDrawerMessage'), null, buttons);
                this.closeDrawer();
            } catch (e) {
                // do nothing
            }
        }

        closeDrawer(): void {
            this.$emit('close');
        }

        checkRoutePermission(): boolean {
            if (this.id === NEW_ID) {
                return true;
            }

            return this.checkPermission('field', ['VIEW'], { id: this.id });
        }
    }
</script>

<template>
    <base-drawer
        class="field-setting-details"
        @click-background="handleCancel"
    >
        <base-drawer-header
            class="field-setting-details__header"
            @close-click="handleCancel"
        >
            {{ $t('fieldSettings.details.title') }}
        </base-drawer-header>
        <base-drawer-body
            class="field-setting-details__body"
        >
            <base-scroll
                size="big"
            >
                <div
                    v-if="isReady"
                    class="field-setting-details__content-wrapper"
                >
                    <el-input
                        v-model="name"
                        :disabled="disabled"
                        :placeholder="$t('fieldSettings.details.name')"
                        class="field-setting-details__input-name"
                    />
                    <field-settings-details-type-select
                        :options="options"
                        :disabled="disabled"
                        :types="Object.keys(types)"
                        @update-options="handleUpdateOptions"
                    />
                    <field-setting-details-multi-values
                        :setting="setting"
                        :disabled="disabled"
                        @update-setting="setSetting"
                    />
                </div>
                <div class="field-setting-details__separator" />
                <div
                    v-if="isReady"
                    class="field-setting-details__content-wrapper"
                >
                    <field-setting-details-preview
                        :setting="setting"
                    />
                    <component
                        :is="types[options.type]"
                        :options="options"
                        :disabled="disabled"
                        @update-options="handleUpdateOptions"
                    />
                </div>
            </base-scroll>
        </base-drawer-body>
        <base-footer
            :cancel-text="$t('fieldSettings.details.cancel')"
            :confirm-text="$t('fieldSettings.details.save')"
            :confirm-disabled="!isValid"
            @cancel="handleCancel"
            @confirm="handleSave"
        />
    </base-drawer>
</template>

<style lang="scss" scoped>
    .field-setting-details {
        $drawer-width: 45 * $base-value + px;

        /deep/ .base-drawer__view {
            width: $drawer-width;
        }

        /deep/ &__header {
            $header-height: 7 * $base-value + px;

            height: $header-height;
            min-height: $header-height;
        }

        &__body {
            padding: 4 * $base-value + px;
        }

        &__content-wrapper {
            $vertical-padding: $big-size;
            $horizontal-padding: 4 * $base-value + px;

            padding: $vertical-padding $horizontal-padding $vertical-padding $horizontal-padding;
        }

        &__separator {
            border-bottom: solid 1px $color-29;
        }

        /deep/ .field-setting-details {

            &__type-select,
            &__sub-type-select,
            &__input-name {
                width: 100%;
                margin-bottom: $middle-size;
            }
        }
    }
</style>
