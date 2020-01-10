const initialState = {
    mainStatistics: {},
    brandStatistics: {},
    productStatistics: {},
    tagStatistics: {},
};

export default function reducers(state = initialState, action) {
    switch (action.type) {
        case 'MAIN_STATISTICS_CHANGE':
            return {
                ...state,
                mainStatistics: action.payload
            }
        case 'BRAND_STATISTICS_CHANGE':
            return {
                ...state,
                brandStatistics: action.payload
            }
        case 'PRODUCT_STATISTICS_CHANGE':
            return {
                ...state,
                productStatistics: action.payload
            }
        case 'TAG_STATISTICS_CHANGE':
            return {
                ...state,
                tagStatistics: action.payload
            }
        default:
            return state;
    }
}