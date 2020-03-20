<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import UserDetails from '@/components/user/UserDetails.vue';
    import {
        Context,
        EditUser,
        User,
    } from '@/types';
    import { namespace } from 'vuex-class';
    import { staticRoles } from '@/utils/enums/userRole';

    const {
        State: DetailsState,
        Getter: DetailsGetter,
        Mutation: DetailsMutation,
        Action: DetailsAction,
    } = namespace('users/details');

    const {
        State: AppState,
    } = namespace('app');

    @Component({
        components: {
            UserDetails,
        },
    })
    export default class UsersDetailsCreate extends Vue {
        @AppState
        context: Context;

        @DetailsState
        user: User;

        @DetailsGetter
        userWasEdited: boolean;

        @DetailsMutation
        setOriginalUser: (user: EditUser) => void;

        @DetailsAction
        createUser: (user: User) => void;

        created(): void {
            this.setOriginalUser({
                enabled: false,
            });
        }

        async handleClose(): Promise<void> {
            if (!this.userWasEdited) {
                this.closeDrawer();

                return;
            }

            try {
                await this.$confirmation(this.$tc('common.messages.saveWarningMessage'));
                await this.handleUserChanged(this.user);
            } catch (e) {
                this.closeDrawer();
            }
        }

        async handleUserChanged(user: User): Promise<void> {
            await this.createUser(user);
            this.$emit('user-created', user);
            this.closeDrawer();
        }

        closeDrawer(): void {
            this.$emit('close');
        }
    }
</script>

<template>
    <user-details
        :header-title="$t('user.information.detailsTitle')"
        full-form
        @close="handleClose"
        @user-changed="handleUserChanged"
    />
</template>
