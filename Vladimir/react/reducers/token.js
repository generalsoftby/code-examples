const initialState = {
    token: null,
};

export default function reducers(state = initialState, action) {
    switch (action.type) {
        case 'CHANGE_TOKEN':
            return {
                ...state,
                token: action.payload
            }
        default:
            return state;

    }
}