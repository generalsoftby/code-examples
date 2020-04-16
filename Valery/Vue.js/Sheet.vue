<script>
import { mapGetters } from "vuex";
import axios from "axios";

import "./Sheet.scss";

import { DAY_OF_WEEK_LABELS, MONTH_NAMES_IN_GENITIVE, MODES_LABELS } from "./Constants";

import Hours from "./Body/Hours";
import Days from "./Body/Days";
import Month from "./Body/Month";
const MODES = { HOURS: 0, DAYS: 1, MONTH: 2 };
const INITIAL_MODE = MODES.HOURS;

export default {
    name: "Sheet",
    props: {},
    data() {
        let currentDate = new Date();

        return {
            MONTH_NAMES_IN_GENITIVE,
            DAY_OF_WEEK_LABELS,
            MODES,
            MODES_LABELS,

            currentDate,
            datePosition: currentDate,
            minDatePosition: null,
            maxDatePosition: null,
            currentMode: INITIAL_MODE,
            updateCurrentDateInterval: null,
            updateCurrentDateIntervalMs: 30000,

            facilities: [],
            facility: null,
        };
    },
    components: {
        Hours,
        Days,
        Month
    },
    async mounted() {
        this.updateCurrentDateInterval = setInterval(() => (this.currentDate = new Date()), this.updateCurrentDateIntervalMs);

        this.facilities = await this.receiveFacilities();
        if (this.facilities && this.facilities.length !== 0) {
            this.facilities[0].id;
        }
    },

    computed: Object.assign(
        mapGetters({
            services: "chessboard/services"
        })
    ),

    beforeDestroy() {
        if (this.updateCurrentDateInterval) {
            clearInterval(this.updateCurrentDateInterval);
        }
    },

    methods: {
        async receiveFacilities() {
            try {
                var result = await axios.get("/chessboard/facilities");
                return result.data.data;
            } catch (ex) {
                console.log("receiveFacilities", ex);
            }
            return null;
        },
        async receiveServices(minDatePosition, maxDatePosition) {
            this.$store.dispatch("chessboard/fetchServices", { facilityId: this.facility });
        },
        moveSheetPrev() {
            this.$refs.body.moveSheetPrev();
        },
        moveSheetNext() {
            this.$refs.body.moveSheetNext();
        },
        updateDatePosition(datePosition) {
            this.datePosition = datePosition;
        },
        goToDay({ year, month, number }) {
            this.datePosition = new Date(year, month, parseInt(number));
            this.currentMode = MODES.DAYS;
        },
        goToCurrentDate() {
            if (this.$refs.body.goToDate) {
                this.datePosition = new Date();
                if (this.currentMode === MODES.HOURS) {
                    this.datePosition.setHours(this.datePosition.getHours(), 0, 0, 0, 0);
                } else {
                    this.datePosition.setHours(0, 0, 0, 0, 0);
                }
                this.$refs.body.goToDate(this.datePosition);
            }
        }
    }
};
</script>

<template>
    <div class="sheet">
        <div class="sheet__controls">
            <el-select
                v-model="facility"
                @change="receiveServices"
            >
                <el-option
                    v-for="facility in facilities"
                    :key="facility.id"
                    :label="facility.name"
                    :value="facility.id"
                ></el-option>
            </el-select>
            <el-button-group>
                <el-button
                    v-for="mode in MODES"
                    :key="mode"
                    @click="currentMode = mode"
                    :type="mode ===  currentMode ? 'primary' : 'default'"
                >{{MODES_LABELS[mode]}}</el-button>
            </el-button-group>
            <el-button-group>
                <el-button @click="moveSheetPrev">назад</el-button>
                <el-button @click="goToCurrentDate">сегодня</el-button>
                <el-button @click="moveSheetNext">вперед</el-button>
            </el-button-group>
        </div>
        <div class="sheet__container">
            <div class="sheet__container__sidebar">
                <div class="sheet__container__sidebar__header">
                    <h1>{{currentDate.toLocaleTimeString().substr(0, 5)}}</h1>
                    <h4>{{currentDate.getDate()}} {{MONTH_NAMES_IN_GENITIVE[currentDate.getMonth()]}} {{currentDate.getFullYear()}} {{DAY_OF_WEEK_LABELS[currentDate.getDay()]}}</h4>
                </div>
                <div class="sheet__container__sidebar__service-categories">
                    <div
                        v-for="(service, serviceIndex) in services"
                        :key="serviceIndex"
                        class="sheet__container__sidebar__service-categories__service-category"
                        :class="{'sheet__container__sidebar__service-categories__service-category-collapsed': service.isCollapsed}"
                        :style="{height: ((service.resources.length + 1) * 50) + 'px'}"
                    >
                        <div
                            class="sheet__container__sidebar__service-categories__service-category__name"
                            v-on:click="collapseService(serviceIndex)"
                        >
                            <span>{{service.name}}</span>
                            <i class="el-icon-caret-top"></i>
                        </div>
                        <div
                            v-for="(resource, index) in service.resources"
                            :key="index"
                            class="sheet__container__sidebar__service-categories__service-category__service-name"
                        >
                            <span>{{resource.name}}</span>
                        </div>
                    </div>
                </div>
            </div>

            <template v-if="facility">
                <Hours
                    v-if="currentMode === MODES.HOURS"
                    ref="body"
                    :facilityId="facility"
                    :services="services"
                    :currentDate="currentDate"
                    :receiveServices="receiveServices"
                    :datePosition="datePosition"
                    :updateDatePosition="updateDatePosition"
                />
                <Days
                    v-else-if="currentMode === MODES.DAYS"
                    ref="body"
                    :facilityId="facility"
                    :services="services"
                    :currentDate="currentDate"
                    :receiveServices="receiveServices"
                    :datePosition="datePosition"
                    :updateDatePosition="updateDatePosition"
                />
                <Month
                    v-else
                    ref="body"
                    :facilityId="facility"
                    :services="services"
                    :currentDate="currentDate"
                    :receiveServices="receiveServices"
                    :datePosition="datePosition"
                    :updateDatePosition="updateDatePosition"
                    :goToDay="goToDay"
                />
            </template>
        </div>
    </div>
</template>