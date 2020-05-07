<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import { Action, Mutation, State } from 'vuex-class';

    import UserDetails from '@/components/user/UserDetails.vue';
    import { EditUser, User } from '@/types';
    import { Watch } from 'vue-property-decorator';
    import { userHttp } from '@/http';

    @Component({
        components: {
            UserDetails,
        },
    })
    /**
     * @group App
     * This component renders a form to edit current user information.
     * We integrated `app/auth` to change current user when some data was changed,
     * and `users/details` to set current user information to form.
     */
    export default class AppUserDetails extends Vue {
        @State(state => state.app.auth.user)
        user: User;

        @Mutation('users/details/setOriginalUser')
        setOriginalUser: (user: EditUser) => void;

        @Action('app/auth/updateUser')
        updateUser: (user: User) => Promise<User>;

        get open(): boolean {
            return !!this.$route.query.userProfile;
        }

        @Watch('open', { immediate: true })
        setUserIfOpenProfile(): void {
            if (this.open) {
                this.setOriginalUser(this.user);
            }
        }

        /**
         * @vuese
         * Close user profile.
         */
        close(): void {
            this.$router.push({
                query: {
                    ...this.$route.query,
                    userProfile: undefined,
                },
            });
        }

        /**
         * @vuese
         * Handler user update.
         * @arg The first parameter is changed user.
         */
        async handleUserUpdate(user: User): Promise<void> {
            const updatedUser = (await userHttp.updateUser(user)).data;
            await this.updateUser(updatedUser);
            this.close();
        }
    }
</script>

<template>
    <user-details
        v-if="open"
        :header-title="$t('user.information.title')"
        @close="close"
        @user-changed="handleUserUpdate"
    />
</template>

<style lang="scss" scoped>

</style>
