export function authUser() {
    return (dispatch) => {
        let hash = (Math.random()*1e32).toString(36);
        localStorage.setItem('authUserHash', hash);
        dispatch(authUserAction(hash));
    };
}

export function authUserAction(hash) {
    return {
        type: 'USER_LOGIN_SUCCESS',
        payload: hash
    };
}

export function changeUserName(userName) {
    return {
        type: 'USER_CHANGE_NAME',
        payload: userName
    };
}