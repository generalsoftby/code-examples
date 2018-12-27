<script>
    import Report from '../../services/Report';

    export default {
        components: {
        },
        data: () => {
            return {
                loading: false,
                reports: []
            };
        },
        props: {
            project: {
                required: true,
                type: Object,
            },
        },
        created() {
            this.loading = true;
            Report.forProject(this.project, {perPage: 5}).then( ({ data }) => {

                this.reports = data.data;
                this.loading = false;
            });

            Echo.private('Project.' + this.project.id).listen('ReportCreated', ({ report }) => {
                report.unseen = true;
                this.reports.unshift(report);
            });
        },
        beforeDestroy() {
            Echo.leave('Project.' + this.project.id);
        }
    };
</script>

<template>
    <md-content class="viewport dashboard-block md-elevation-2">
        <md-toolbar :md-elevation="1" class="md-dense">
            <span class="md-title">{{ project.title }}</span>
        </md-toolbar>
        <md-list class="md-dense">
            <template  v-for="report in reports">
                <md-subheader>
                    <date-time :datetime="report.created_at"></date-time>:
                    <span>{{ report.message }}</span>
                </md-subheader>
                <md-list-item>
                    <div class="md-list-item-text">
                        <span class="text-danger mb-0">{{ report.class }}</span>
                    </div>
                </md-list-item>
                <md-list-item>
                    <a v-bind:href="report.url" class="text-info mb-0 text-small" target="_blank">{{ report.url }}</a>
                </md-list-item>
                <md-divider></md-divider>
            </template>
        </md-list>
    </md-content>
</template>

<style scoped>
    .md-list {
        margin-bottom: 0;
        padding-bottom: 0;
    }
</style>

<style>
    .dashboard-block .md-list-item-content {
        min-height: 0 !important;
    }
</style>
