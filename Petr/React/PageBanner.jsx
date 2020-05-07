import React from "react";
import {Container} from "reactstrap";
import Breadcrumb from "@/globalComponents/BreadCrumbs";

export default function (props) {
    const style = {
        banner: {
            background: 'none',
            width: '100%',
            position: 'absolute',
            left: 0,
            height: '400px',
            zIndex: -1
        }
    };

    style.banner.backgroundImage = 'url(/storage/app/media/'+ props.text_banner.image + ')';
    style.banner.backgroundSize = 'cover';
    style.banner.backgroundPosition = 'center center';
    style.banner.backgroundRepeat = 'no-repeat';

    let textColorStyle = {
        color: (props.text_banner.color ? props.text_banner.color : '#000000')
    };

    return (
        <div className="banner">
            {
                props.breadCrumbs && (
                    <div className="service-breadcrumbs">
                        <Container className="p-sm-0">
                            <Breadcrumb
                                items={props.breadCrumbs}
                                itemName='name'
                                itemLink='url'
                                separator={
                                    <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.7969 7.5C15.7969 7.63542 15.7448 7.75521 15.6406 7.85938L8.35938 15.1406C8.25521 15.2448 8.13542 15.2969 8 15.2969C7.86458 15.2969 7.74479 15.2448 7.64062 15.1406L0.359375 7.85938C0.255208 7.75521 0.203125 7.63542 0.203125 7.5C0.203125 7.36458 0.255208 7.24479 0.359375 7.14062L1.14062 6.35938C1.24479 6.25521 1.36458 6.20312 1.5 6.20312C1.63542 6.20312 1.75521 6.25521 1.85938 6.35938L8 12.5L14.1406 6.35938C14.2448 6.25521 14.3646 6.20312 14.5 6.20312C14.6354 6.20312 14.7552 6.25521 14.8594 6.35938L15.6406 7.14062C15.7448 7.24479 15.7969 7.36458 15.7969 7.5Z" fill="black"/>
                                    </svg>
                                }
                                classNamePrefix='service-breadcrumbs'
                            />
                        </Container>
                    </div>
                )
            }

            <div>
                <div className="information">
                    <div className="h1">
                        <h1 style={textColorStyle}>{props.text_banner.title}</h1>
                    </div>
                    <div style={textColorStyle} className="description">
                        {props.text_banner.text}
                    </div>
                </div>
            </div>

            <div className="calculator-banner" style={style.banner} />
        </div>
    )
}