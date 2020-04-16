import React, {PropTypes} from 'react';
import classNames from 'classnames';
import blacklist from 'blacklist';
import ShallowCompare from 'react-addons-shallow-compare';

import './ButtonControl.scss';

const BUTTON_TYPES = [
    'crystal',
    'sand',
    'sky',
    'forest',
    'stone',
    'blackLink',
    'blueLink',
    'backLink',
    'darkStone',
    'white',
];

const BUTTON_SIZES = ['s', 'm', 'l'];

class ButtonControl extends React.Component {

    static propTypes = {
        block: React.PropTypes.bool,
        className: React.PropTypes.string,
        href: React.PropTypes.string,
        component: React.PropTypes.node,
        isActive: React.PropTypes.bool,
        size: React.PropTypes.oneOf(BUTTON_SIZES),
        view: React.PropTypes.oneOf(BUTTON_TYPES),
        leftIcon: React.PropTypes.any,
        rightIcon: React.PropTypes.any,
        shadow: React.PropTypes.bool,
    }

    static defaultProps = {
        view: 'sand',
        shadow: false,
    }

    shouldComponentUpdate(nextProps, nextState) {
        return ShallowCompare(this, nextProps, nextState);
    }

    render() {

        var componentClass = classNames(
            'ButtonControl',
            'ButtonControl-' + this.props.view,
            {'ButtonControl-text': !this.props.leftIcon && !this.props.rightIcon},
            (this.props.size ? 'ButtonControl-' + this.props.size : 'ButtonControl-s'),
            {
                'Button-block': this.props.block,
                'is-active': this.props.isActive,
            },
            (this.props.shadow ? 'ButtonControl-shadow' : ''),
            this.props.className
        );

        var props = blacklist(this.props, 'component', 'className', 'view', 'address', 'leftIcon', 'rightIcon', 'shadow');
        props.className = componentClass;

        if (this.props.component) {
            return React.cloneElement(this.props.component, props);
        }

        var tag = 'button';
        if (props.href) {
            tag = 'a';
            delete props.type;
        }

        var children = this.props.children;

        if ((this.props.leftIcon || this.props.rightIcon) && typeof this.props.children === 'string') {

            children = [
                <div key="buttonText" className={`ButtonControl-text`}>
                    {children}
                </div>,
            ];

            if (this.props.leftIcon) {
                children.unshift(
                    <div key="icon1" className="ButtonControl-lefticon">
                        {this.props.leftIcon}
                    </div>
                );
            }

            if (this.props.rightIcon) {
                children.push(
                    <div key="icon2" className="ButtonControl-righticon">
                        {this.props.rightIcon}
                    </div>
                );
            }
        }

        return React.createElement(tag, props, children);
    }
}

export default ButtonControl;
