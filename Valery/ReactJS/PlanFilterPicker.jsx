import React, { PureComponent } from 'react';
import { Radio } from 'antd';
import { connect } from 'react-redux';

import { filterPlan } from '@/actions/sphera';
import style from './planFilterPicker.scss';

class PlanFilterPicker extends PureComponent {
    onChangeFilter = (e) => {
        this.props.filterPlan(e.target.value);
    }

    render() {
        return (
            <div className={style.planFilterPicker}>
                <span>Выводить:</span>
                <Radio.Group onChange={this.onChangeFilter.bind(this)} value={this.props.filter || 'ALL_POINTS'}>
                    <Radio value={'ALL_POINTS'}>Все</Radio>
                    <Radio value={'FILTER_BY_DATE'}>{moment(this.props.currentSpheraDate).format('DD.MM.YYYY')}</Radio>
                </Radio.Group>
            </div>
        );
    }
}


const mapStateToProps = state => ({
    filter: state.plan.Filter,
    currentSpheraDate: state.sphera.DateTimeCreated
});

export default connect(mapStateToProps,
    { filterPlan }
)(PlanFilterPicker);