export function updateToken() {

    return async (dispatch) => {

        let response = await fetch('http://domain.name', {
            mode: "cors",
            credentials: "include",
        });

        let responseJSON = await response.json();

        switch (response.status) {
            case 200:
                dispatch(updateTokenAction(responseJSON.token));
                break;
            default:
                alert('no connection to server');
        }

    };

}

export function updateTokenAction(token) {
    return {
        type: 'CHANGE_TOKEN',
        payload: token
    };
}