<script>

    import ProjectReport from './Dashboard/ProjectReport.vue';
    import Project from '../services/Project';

    export default {
        data: () => {
            return {
                loading: false,
                projects: []
            }
        },
        methods: {
            getProjects() {
                this.loading = true;

                Project.my().then( ({data}) => {
                    this.projects = data;
                    this.loading = false;
                });
            }
        },
        created() {
            this.getProjects();
        },
        components: {
            ProjectReport
        }
    };
</script>
<template>
    <div class="md-layout md-gutter">
        <div v-for="project in projects" style="margin-bottom: 20px;" class="md-layout-item md-size-33 md-small-size-100 ">
            <project-report :project="project">
            </project-report>
        </div>
    </div>
</template>
