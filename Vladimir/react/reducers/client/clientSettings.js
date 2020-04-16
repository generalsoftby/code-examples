const initialState = {
    mainFilterDateRange: {
        start: null,
        stop: null,
        last: '1y', // 7d 1m 1y
    },
};

export default function reducers(state = initialState, action) {
    switch (action.type) {
        case 'CLIENT_SETTINGS_MAIN_FILTER_DATE_RANGE':
            return {
                ...state,
                mainFilterDateRange: action.payload
            }
        default:
            return state;
    }
}