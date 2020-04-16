import React, {Component} from 'react';
import Select, { components } from 'react-select';
import { cssSimpleSelect } from "./CssSimpleSelect";

class SimpleSelect extends Component {

    /*
    componentDidUpdate(prevProps, prevState) {
        if (this.props.val !== prevProps.val) {
            this.setState({ value: this.props.val });
        }
    }

     */

    render() {
        const DropdownIndicator = props => {
            return (
                components.DropdownIndicator && (
                    <components.DropdownIndicator {...props}>
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="7.13281" cy="7.06006" r="6.5" stroke="#3693FF"/>
                            <path d="M9.13281 7.56006H7.59702H6.6686H5.13281V6.56006H6.6686L7.59702 6.56012L9.13281 6.56006V7.56006Z" fill="#3693FF"/>
                        </svg>
                    </components.DropdownIndicator>
                )
            );
        };

        return (
            <Select
                {...this.props}
                styles={ cssSimpleSelect(this.props.heightSelect) }
                components={{ DropdownIndicator }}
            />
        );

    }
}

export default SimpleSelect;


