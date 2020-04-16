import { mainStatistics } from "../../actions/statistics/mainStatistics";
import { brandStatistics } from "../../actions/statistics/brandStatistics";
import { productStatistics } from "../../actions/statistics/productStatistics";
import { tagStatistics } from "../../actions/statistics/tagStatistics";

export function updateStatistics() {
    return (dispatch) => {
        dispatch(mainStatistics());
        dispatch(brandStatistics());
        dispatch(productStatistics());
        dispatch(tagStatistics());
    };
}






