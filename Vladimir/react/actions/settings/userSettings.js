import { changeUserName } from "../../actions/auth/authUser";
import { logoutUser } from "../../actions/auth/logoutUser";
import { contextBrand } from "../../actions/settings/userContext";

export function userSettings() {

    return async (dispatch) => {

        let response = await fetch('http://domain.name', {
            mode: "cors",
            credentials: "include",
        });

        let responseJSON = await response.json();

        switch (response.status) {
            case 200:
                dispatch(changeUserName(responseJSON.user.name));
                dispatch(userSettingsLeftSidebarMenuAction(responseJSON.menu));

                //clients
                const optionsClients = [];
                const valueClient = { value: null, label: null };
                let topicClients = responseJSON.topics.clients;
                if(topicClients && topicClients.list){
                    topicClients.list.forEach ((item, key) => {
                        optionsClients.push({
                            value: item.id,
                            label: item.name,
                        });
                    });
                }
                if(topicClients && topicClients.active){
                    let clientActiveName = topicClients.active;
                    valueClient.label = clientActiveName;
                    optionsClients.forEach ((item, key) => {
                        if(item.label === clientActiveName)
                            valueClient.value = item.value;
                    });
                }
                dispatch(userSettingsTopicClientsAction({
                    value: valueClient,
                    options: optionsClients,
                }));


                //brands
                const optionsBrands = [];
                const valueBrand = { value: null, label: null };
                let topicBrands = responseJSON.topics.brands;
                if(topicBrands && topicBrands.list){
                    topicBrands.list.forEach ((item, key) => {
                        optionsBrands.push({
                            value: item.id,
                            label: item.name,
                        });
                    });
                }
                if(topicBrands && topicBrands.active){
                    let brandActiveName = topicBrands.active;
                    valueBrand.label = brandActiveName;
                    optionsBrands.forEach ((item, key) => {
                        if(item.label === brandActiveName)
                            valueBrand.value = item.value;
                    });
                }

                dispatch(userSettingsTopicBrandsAction({
                    value: valueBrand,
                    options: optionsBrands,
                }));


                break;
            default:
                dispatch(logoutUser());
        }
    };
}

export function userSettingsLeftSidebarMenuAction(menu) {
    return {
        type: 'USER_SETTINGS_CHANGE_LEFT_SIDEBAR_MENU',
        payload: menu
    };
}

export function userSettingsTopicBrandsValue(data) {
    return async(dispatch, getState) => {
        let optionBrands = getState().userSettings.topicBrands.options;
        await dispatch(userSettingsTopicBrandsAction({
            value: data,
            options: optionBrands,
        }));
        await dispatch(contextBrand());
    };
}

export function userSettingsTopicBrandsAction(data) {
    return {
        type: 'USER_SETTINGS_CHANGE_TOPIC_BRANDS',
        payload: data
    };
}

export function userSettingsTopicClientsAction(data) {
    return {
        type: 'USER_SETTINGS_CHANGE_TOPIC_CLIENTS',
        payload: data
    };
}