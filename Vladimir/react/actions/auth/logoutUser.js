export function logoutUser() {
    return async (dispatch, getState) => {
        let token = getState().token.token;

        await fetch('http://domain.name', {
            mode: "cors",
            method: "POST",
            headers: {
                "X-CSRFToken": token,
            },
            credentials: "include",
        });

        localStorage.setItem('authUserHash', '');
        dispatch(logoutUserAction());

    };
}

export function logoutUserAction() {
    return {
        type: 'USER_LOGOUT',
        payload: null
    };
}
