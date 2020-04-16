/*

switch-to-client/{client_id}/
switch-to-brand/{brand_id}/
switch-to-product/{product_id}/
switch-to-country/{country_id}/


 */

import {updateStatistics} from "../statistics/updateStatistics";


export function contextBrand() {
    return async (dispatch, getState) => {

        let token = getState().token.token;
        let brandId = getState().userSettings.topicBrands.value.value;

        let response = await fetch('http://domain.name', {
            mode: "cors",
            method: "POST",
            headers: {
                "X-CSRFToken": token,
            },
            credentials: "include",
        });

        let responseJSON = await response.json();

        switch (response.status) {
            case 200:
                console.log(responseJSON)
                await dispatch(updateStatistics());




                break;
            default:
                break;
        }
    };
}
