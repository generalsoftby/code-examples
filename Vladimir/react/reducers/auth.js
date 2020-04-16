const initialState = {
    authUserHash: localStorage.getItem('authUserHash'),
    userName: "",
};

export default function reducers(state = initialState, action) {
    switch (action.type) {
        case 'USER_LOGIN_SUCCESS':
            return {
                ...state,
                authUserHash: action.payload
            }
        case 'USER_LOGOUT':
            return {
                ...state,
                authUserHash: ''
            }
        case 'USER_CHANGE_NAME':
            return {
                ...state,
                userName: action.payload
            }
        default:
            return state;
    }
}