import store from "@/store";
import MutateService from "@/shared/calculation/src/services/mutateService";
import RequestsService from "@/shared/calculation/src/services/requestsService";
import {
    getActionContextFromApiSnapshot,
    getActionContextFromStoreSnapshot,
    getCurrentCalculatorApiSnapshot,
} from "@/shared/calculation/src/services/snapshotService";
import {
    getAttribute,
    getAttributesData,
} from "@/shared/calculation/src/services/attributesService";

export const CHOICE_ATTRIBUTE = "CHOICE_ATTRIBUTE";
export const SWITCH_DISABLE_FIELD = "SWITCH_DISABLE_FIELD";
export const CHANGE_USER_VALUES = "CHANGE_USER_VALUES";
export const CHOICE_ATTRIBUTE_WITH_TEXT = "CHOICE_ATTRIBUTE_WITH_TEXT";

export function choiceAttribute(attributeId, attributeValue, type, contextType, contextId, browserHistory = null)
{
    const calculatorSnapshot = getCurrentCalculatorApiSnapshot(attributeId, attributeValue, type, contextType, contextId);
    const actionContext = getActionContextFromApiSnapshot(calculatorSnapshot, contextType, contextId);
    const typesMap = {
        attribute: "attributes",
        option: "options",
    };

    if(!(type in typesMap))
    {
        throw new Error(`Unsupported attribute type: ${type}`);
    }

    actionContext[typesMap[type]] = getAttributesData(actionContext[typesMap[type]], attributeId, attributeValue);

    return RequestsService.calculateRequest(calculatorSnapshot, (data) =>
        ({
            type: CHOICE_ATTRIBUTE,
            id: attributeId,
            value: attributeValue,
            attributeType: type,
            contextType: contextType,
            contextId: contextId,
            data,
        }),
        null,
        null,
        null,
        browserHistory);
}

export function choiceAttributeWithText(attributeId, attributeValue, type, contextType, contextId)
{
    const calculatorSnapshot = getCurrentCalculatorApiSnapshot();
    const actionContext = getActionContextFromApiSnapshot(calculatorSnapshot, contextType, contextId);

    switch (type)
    {
        case "attribute": {
            actionContext.attributes = getAttributesData(actionContext.attributes, attributeId, attributeValue);
            break;
        }
        case "option": {
            actionContext.options = getAttributesData(actionContext.options, attributeId, attributeValue);
            break;
        }
        default:
            console.error(`Unsupported attribute type: ${type}`);
    }

    return RequestsService.calculateRequest(calculatorSnapshot, (data) =>
        ({
            type: CHOICE_ATTRIBUTE_WITH_TEXT,
            id: attributeId,
            value: attributeValue,
            contextType: contextType,
            attributeType: type,
            contextId: contextId,
            data,
        }));
}

export function changeUserValue(id, valueId, value, contextType, contextId)
{
    const calculatorSnapshot = getCurrentCalculatorApiSnapshot();
    const actionContext = getActionContextFromApiSnapshot(calculatorSnapshot, contextType, contextId);
    const storeActionContext = getActionContextFromStoreSnapshot(store.getState(), contextType, contextId);

    actionContext.options = storeActionContext
        .attributes
        .filter(item => item.type === "option")
        .map(option => option.fieldType === "select_with_text"
            ? {
                id: MutateService.getOriginalId(option),
                values: option.userValues.map(userValue =>
                    ({
                        id: userValue.id,
                        user_values: userValue.id === valueId ? [value] : [userValue.value]
                    })
                )
            }
            : getAttribute(option.defaultValue, option)
        );

    return RequestsService.calculateRequest(calculatorSnapshot, (data) =>
        ({
            type: CHANGE_USER_VALUES,
            contextType: contextType,
            contextId: contextId,
            data,
            id,
            value,
            valueId,
        }));
}

export const switchDisableField = (attrId, checked) => ({
    type: SWITCH_DISABLE_FIELD,
    attrId,
    checked,
});
