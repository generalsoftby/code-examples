import { updateStatistics } from "../../actions/statistics/updateStatistics";

export function clientSettingsMainFilterDateRange(dateRange) {
    return async (dispatch) => {
        await dispatch(clientSettingsMainFilterDateRangeAction(dateRange));
        await dispatch(updateStatistics());
    };
}

export function clientSettingsMainFilterDateRangeAction(dateRange) {
    return {
        type: 'CLIENT_SETTINGS_MAIN_FILTER_DATE_RANGE',
        payload: dateRange
    };
}
