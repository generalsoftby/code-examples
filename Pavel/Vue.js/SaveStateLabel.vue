<script>
    import FieldValue from '../fields/values/FieldValue';
    import FieldValueCalendar from '../fields/values/FieldValueCalendar';
    import FieldValueMultiSelect from '../fields/values/FieldValueMultiSelect';

    export default {
        name: 'SaveStateLabel',
        components: {
            FieldValueMultiSelect,
            FieldValueCalendar,
            FieldValue
        },
        props: {
            label: {
                type: Object,
                default() {
                    return {
                        fields: []
                    }
                }
            },
            settings: {
                type: Object,
                required: true
            }
        },

        computed: {
            simpleFields() {
                const notSimpleFieldTypes = ['textarea', 'address', 'bankaccount'];

                return this.label.fields.filter((f) => {
                    return !notSimpleFieldTypes.includes(f.type);
                });
            },

            addresses() {
                return this.label.fields.filter((f) => {
                    return f.type === 'address';
                });
            },

            bankaccounts() {
                return this.label.fields.filter((f) => {
                    return f.type === 'bankaccount';
                });
            },

            textareas() {
                return this.label.fields.filter((f) => {
                    return f.type === 'textarea';
                });
            },

        },

        methods: {
            getDisplayedName(field) {
                const settings = this.settings.fieldSettings.find(fs => {
                    return field.name === fs.name;
                });

                return settings.displayedName;
            },

            getFilledProps(field) {
                const filledProps = {};

                _.forEach(field.value, (value, key) => {
                    if (value !== null) {
                        filledProps[key] = value;
                    }
                });

                return filledProps;
            },

            getFieldValueComponent(field) {
                let namePrefix = '';

                switch (field.type) {
                    case 'calendar':
                        namePrefix = 'Calendar';
                        break;
                    case 'multiselect':
                        namePrefix = 'MultiSelect';
                        break;
                }

                return 'FieldValue' + namePrefix;
            },

            getFieldSettings(field) {
                const name = field.name || name;

                return this.settings.fieldSettings.find(fs => fs.name === name);
            }
        }
    }
</script>

<template>
    <el-main>
        <el-row>
            <el-col v-for="field in simpleFields"
                    :key="field.uuid"
                    :span="8"
                    class="field-row">
                <b>{{ getDisplayedName(field) }}:</b>
                <br/>
                <component :is="getFieldValueComponent(field)" :field="field" :options="getFieldSettings(field).options"/>
            </el-col>
        </el-row>

        <el-row>
            <el-col v-for="field in textareas"
                    :key="field.uuid"
                    :span="24"
                    class="field-row">
                <b>{{ getDisplayedName(field) }}:</b>
                <br/>
                {{ field.value }}
            </el-col>
        </el-row>

        <el-row>
            <el-col v-for="field in addresses"
                    :key="field.uuid"
                    :span="8"
                    class="field-row">
                <b>{{ getDisplayedName(field) }}:</b>
                <br/>
                <span v-for="(value, key) in getFilledProps(field)" class="nested">
                    <b>{{ key }}</b>: {{ value }}
                    <br/>
                </span>
            </el-col>
        </el-row>

        <el-row>
            <el-col v-for="field in bankaccounts"
                    :key="field.uuid"
                    :span="8"
                    class="field-row">
                <b>{{ getDisplayedName(field) }}:</b>
                <br/>
                <span v-for="(value, key) in getFilledProps(field)" class="nested">
                    <b>{{ key }}</b>: {{ value }}
                    <br/>
                </span>
            </el-col>
        </el-row>
    </el-main>
</template>

<style scoped>
    .field-row {
        margin-bottom: 5px;
        margin-top: 5px;
    }

    .nested {
        padding-left: 10px;
    }
</style>
