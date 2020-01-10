
export function brandStatistics() {

    return async (dispatch, getState) => {

        let mainFilterDateRange = getState().clientSettings.mainFilterDateRange;
        let strGetParams = '';
        if(mainFilterDateRange.last)
            strGetParams = '?last=' + mainFilterDateRange.last;
        if(!strGetParams && mainFilterDateRange.start && mainFilterDateRange.stop){
            strGetParams = '?start=' + mainFilterDateRange.start + '&stop=' + mainFilterDateRange.stop;
        }

        let response = await fetch('http://domain.name' + strGetParams, {
        //let response = await fetch('http://domain.name', {
            mode: "cors",
            credentials: "include",
        });

        let responseJSON = await response.json();

        switch (response.status) {
            case 200:
                dispatch(brandStatisticsAction(responseJSON.result));
                break;
            default:
                break;
        }
    };
}


export function brandStatisticsAction(statistics) {
    return {
        type: 'BRAND_STATISTICS_CHANGE',
        payload: statistics
    };
}




