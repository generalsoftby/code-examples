import classNames from 'classnames';
import React, {Component, PropTypes} from 'react';
import IconSVG from 'svg-inline-react';
import AnimakitExpander from 'animakit-expander';
import ga from 'react-ga';

import './DetailsItem.scss';

class DetailsItem extends Component {

	static propTypes = {
        expanded: React.PropTypes.bool,
        title: React.PropTypes.string,
        description: React.PropTypes.string,
        duration: React.PropTypes.number,
        className: React.PropTypes.string,
        onClick: React.PropTypes.func,
        gaLabel: React.PropTypes.string,
    };

    static defaultProps = {
        expanded: false,
        title: '',
        description: '',
        duration: 300,
    };

	state = {
		expanded: this.props.expanded,
	};

	_onClick = (event) => {
        if (this.props.onClick) {
            this.props.onClick(event);
        };

        if (this.props.gaLabel) {
        	this._gaClick(this.props.gaLabel);
        };

        this._toggle();
    };

    _gaClick = (gaLabel) => {
    	ga.event({
			category: 'clicks',
			action: 'click_site_info_booking_step_1',
			label: gaLabel,
			value: 1,
		});
    };

	_toggle = () => {
		this.setState({expanded: !this.state.expanded})
	};

	render() {

		var detailsItemClass = classNames(
			'DetailsItem',
			{'DetailsItem--opened': this.state.expanded},
			this.props.className
		);

		return (
			<div className={detailsItemClass} onClick={this._onClick}>
				<div className="DetailsItem-title">
					{this.props.title}
					<IconSVG src={require('!svg-inline?removeSVGTagAttrs=false!./arrow_down.svg')} />
				</div>
				<AnimakitExpander expanded={this.state.expanded} duration={this.props.duration}>
					<div className="DetailsItem-description">{this.props.description || this.props.children}</div>
				</AnimakitExpander>
			</div>
		);
	}
}

export default DetailsItem;
