import React from "react";
import {NavLink} from "react-router-dom";

export default function (props) {
    const itemClassName = props.classNamePrefix ? props.classNamePrefix+'__item' : '25ea85';
    return (
        <div className={props.classNamePrefix ? props.classNamePrefix+'__wrapper' : 'fe3988'}>
            {props.items.map((el,index,array)=>{
                return (
                    <div key ={index} className={props.classNamePrefix ? props.classNamePrefix+'__item-wrapper' : 'f487e4'}>
                        {
                            el[props.itemLink] ?
                                <NavLink
                                    className={itemClassName}
                                    to={el[props.itemLink]}>
                                    {el[props.itemName]}
                                </NavLink>
                                :
                                <span
                                    className={itemClassName+' disabled'}>
                                    {el[props.itemName]}
                                </span>
                        }
                        {
                            index !== array.length-1 && (
                                <span className={props.classNamePrefix ? props.classNamePrefix+'__separator' : '097eb2'}>
                                    {props.separator}
                                </span>
                            )
                        }
                    </div>
                )
            })}
        </div>
    )
}