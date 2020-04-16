const initialState = {
    leftSidebarMenu: [],
    topicClients: {
        value: {
            value: null,
            label: null,
        },
        options: [],
    },
    topicBrands: {
        value: {
            value: null,
            label: null,
        },
        options: [],
    },



    topics: {},
};

export default function reducers(state = initialState, action) {
    switch (action.type) {
        case 'USER_SETTINGS_CHANGE_LEFT_SIDEBAR_MENU':
            return {
                ...state,
                leftSidebarMenu: action.payload
            }
        case 'USER_SETTINGS_CHANGE_TOPIC_BRANDS':
            return {
                ...state,
                topicBrands: action.payload
            }
        case 'USER_SETTINGS_CHANGE_TOPIC_CLIENTS':
            return {
                ...state,
                topicClients: action.payload
            }

        default:
            return state;
    }
}