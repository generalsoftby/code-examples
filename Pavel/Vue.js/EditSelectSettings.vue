<script>
    import Vue from 'vue';

    export default {
        components: {
        },
        name: 'EditSelectSettings',
        props: {
            options: {
                type: Object,
                required: true
            }
        },
        data: function () {
            return {
                newOption: '',
            };
        },
        created() {
            if (!this.options.dropdown) {
                Vue.set(this.options, 'dropdown', []);
            }
        },
        methods: {
            removeOption(index) {
                this.options.dropdown.splice(index, 1);
            },
            addOption() {
                this.options.dropdown.push(this.newOption);
                this.newOption = '';
            },
        },
        watch: {
            options: {
                handler: function () {
                    this.$emit('changed', this.options);
                },
                deep: true
            }
        },

    }
</script>

<template>
    <div>
        <div>
            <el-tag :closable="true" v-for="(item, index) in options.dropdown" :key="index" @close="removeOption(index)">
                {{ item }}
            </el-tag>
            <el-input placeholder="New option" v-model="newOption" class="tag-input">
                <el-button slot="append" icon="el-icon-plus" :disabled="newOption.length === 0" @click="addOption"></el-button>
            </el-input>
        </div>
    </div>
</template>

<style lang="css" scoped>
    .tag-input {
        width: 200px;
        height: 28px;
        line-height: 28px;
        margin-top: 5px;
    }

    .el-tag {
        margin-top: 5px;
    }
</style>
