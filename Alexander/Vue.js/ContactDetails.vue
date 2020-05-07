<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import { namespace } from 'vuex-class';

    import ContactDetailsHeader from '@/components/contacts/details/ContactDetailsHeader.vue';
    import ContactDetailsNavigation from '@/components/contacts/details/ContactDetailsNavigation.vue';
    import BaseDrawer from '@/common/components/drawer/BaseDrawer.vue';
    import { Contact, Label, OperationsAccess } from '@/types';
    import { ListApiResponse } from '@/types/api';
    import { notifySuccess } from '@/utils/notificationsHelper';

    const {
        Action: DetailsAction,
        State: DetailsState,
        Getter: DetailsGetter,
    } = namespace('contacts/details');

    const {
        Mutation: InformationMutation,
        State,
        Getter: InformationGetter,
    } = namespace('contacts/details/information');

    const {
        Mutation: ListMutation,
        Action: ListAction,
    } = namespace('contacts/list');

    const {
        Getter: ContactsGetter,
    } = namespace('contacts');

    @Component({
        components: {
            ContactDetailsHeader,
            ContactDetailsNavigation,
            BaseDrawer,
        },
    })
    /**
     * @group Contacts
     * Contact details drawer
     * We integrated the vuex for `contacts/details/information`, `contacts`, `contacts/list`
     */
    export default class ContactDetails extends Vue {
        @ContactsGetter
        keywordsPermissions: OperationsAccess;

        @ContactsGetter
        labelManagementPermission: OperationsAccess;

        @ContactsGetter
        contactPermission: OperationsAccess;

        @DetailsState
        contact: Contact;

        @DetailsGetter('isNewContact')
        isNewContact: boolean;

        @DetailsAction
        getContact: (id: string) => Promise<Contact>;

        @DetailsAction
        createNewContact: (type: string) => Promise<Contact>;

        @DetailsAction
        clearStorage: () => Promise<void>;

        @DetailsAction
        updateLabel: (label: Label) => Promise<Label>;

        @State
        changed: boolean;

        @InformationMutation('setLabelChanged')
        setLabelChanged: (changed: boolean) => void;

        @State
        labelValue: Label;

        @ListAction
        getContacts: () => Promise<ListApiResponse<Contact>>;

        @ListMutation
        updateContact: (contact: Contact) => void;

        @InformationGetter('getLabelEdit')
        editLabel: boolean;

        mode = 'edit';

        created(): void {
            const { id } = this.$route.params;

            if (id === 'new') {
                const type = this.$route.query.type === 'person' ? 'B2C' : 'B2B';
                this.createNewContact(type);
                this.mode = 'create';
            } else if (!this.contactReady) {
                this.getContact(id)
                    .catch(() => {
                        this.$router.replace({ name: 'pages.notFound' });
                    })
                ;
            }
            this.setLabelChanged(false);
        }

        beforeRouteUpdate(to, from, next): void {
            const { id } = to.params;

            if (id !== from.params.id) {
                if (id === 'new') {
                    next({
                        name: 'contacts.list',
                    });

                    return;
                }

                this.getContact(id).then(() => {
                    next();
                });
            } else {
                next();
            }
        }

        beforeRouteLeave(to, from, next): void {
            this.clearStorage();
            next();
        }

        get labels(): Label[] {
            const { labels } = this.contact || {};
            return labels || [];
        }

        get keywords(): string[] {
            const { keywords } = this.contact || {};
            return keywords || [];
        }

        get contactReady(): boolean {
            return this.contact
                ? this.contact.id === this.$route.params.id || this.isNewContact
                : false;
        }

        async handleClose(): Promise<void> {
            const buttons = {
                confirmText: this.$t('common.yes').toString(),
                cancelText: this.$t('common.no').toString(),
            };
            try {
                if (this.editLabel) {
                    try {
                        await this.$confirmation(this.$tc('common.messages.leaveDrawerMessage'), null, buttons);
                        this.closeDrawer();
                    } catch (e) {
                        // on cancell
                    }
                } else {
                    this.closeDrawer();
                    if (this.changed) {
                        if (this.mode === 'create') {
                            notifySuccess(this.$tc('common.notifications.create.contact'));
                        } else {
                            notifySuccess(this.$tc('common.notifications.change'));
                        }
                    }
                }
            } catch (e) {
                // only close confirmation
            }
        }

        closeDrawer(): void {
            this.$router.push({ name: 'contacts.list' });
        }
    }
</script>

<template>
    <base-drawer
        class="contact-details__details-view"
        @click-background="handleClose"
    >
        <!-- Header -->
        <div class="contact-details__details-view__header">
            <contact-details-header
                class="contact-details-header"
                :contact="contact"
                :keywords-permissions="keywordsPermissions"
                :label-management-permission="labelManagementPermission"
                :contact-permission="contactPermission"
                @close-contact-details="handleClose"
            />
        </div>
        <div class="contact-details__details-view__inner-container">
            <!-- Navigation -->
            <div class="contact-details__details-view__navigation-panel">
                <contact-details-navigation
                    :keywords-permissions="keywordsPermissions"
                    :keywords="keywordsPermissions.view ? keywords : null"
                />
            </div>
            <!-- Content -->
            <div class="contact-details__details-view__content">
                <router-view
                    v-if="contactReady"
                />
            </div>
        </div>
        <modals-container />
    </base-drawer>
</template>

<style lang="scss" scoped>
    /deep/ .drawer__view {
        $width: 960px;
        width: $width;
    }

    .contact-details {
        &-header {
            padding: 0 $big-size;
            border-bottom: 1px solid $gray-light;
        }

        &__details-view {
            &__header {
                display: block;
                width: 100%;
            }

            // Navigation & Contact Details View Container
            &__inner-container {
                display: flex;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }

            // Navigation
            &__navigation-panel {
                $width: 259px;

                flex: 1 1 $width;
                max-width: $width;
                min-height: calc(100vh - 70px); // Top Panel Height
                background-color: $background-color-base;
                border-right: 2px solid $border-color-light;
                box-sizing: border-box;
            }

            // Content
            &__content {
                position: relative;
                flex: 2 2 73%;
                height: 100%;
                overflow: hidden;
            }
        }
    }
</style>
