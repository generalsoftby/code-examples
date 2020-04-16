import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import classNames from 'classnames';
import { CategoryType } from 'types';
import ga from 'react-ga';
import { decline } from 'core/utils';
import ActionCreators from 'actions/ActionCreators';
import Cargo from 'components/Cargo';
import CategoryStore from 'stores/CategoryStore';
import CargosInfo from 'components/CargosInfo';
import TotalMeasurementsPaneControl from 'components/TotalMeasurementsPaneControl';

import './Cargos.scss';

class Cargos extends Component {

    static propTypes = {
        cargos: PropTypes.instanceOf(Immutable.List),
        hasEditingCargo: PropTypes.bool,
        catergoryCargos: PropTypes.arrayOf(Immutable.Record),
        defaultCargo: PropTypes.instanceOf(Immutable.Record),
        addCargo: PropTypes.bool,
        selectedCategory: PropTypes.instanceOf(CategoryType),
    }

    static defaultProps = {
        addCargo: true
    }

    _onAddCargoClick = () => {

        ga.event({
            category: 'clicks',
            action: 'click_button_add_cargo_booking_step_2',
            label: CategoryStore.getSelected().name,
            value: 1,
        });

        if (this.props.catergoryCargos.length === 0) {
            ActionCreators.addCargo(this.props.defaultCargo);
        } else {
            ActionCreators.addSubcategory();
        }
    }

    _renderAllCargos = () => {
        return (this.props.cargos.map((cargo, key) => {
            return (
                <div className="Cargos-item" key={key}>
                    <Cargo cargo={cargo} id={key} selectedCategory={this.props.selectedCategory} />
                </div>
            );
        }));
    }

    _renderAddCargoButton = (totalCargos) => {
        if ((totalCargos || this.props.catergoryCargos.length === 0)
            && this.props.addCargo) {
            const addCargoText = this.props.hasEditingCargo ? 'Новый груз' : '+ Добавить груз';
            const addCargoClass = classNames([
                'Cargos-addCargo',
                {
                    '-new': this.props.hasEditingCargo
                }
            ]);

            return (
                <div className={addCargoClass}
                    onClick={this._onAddCargoClick}>
                    <span className="Cargos-addCargoText">{addCargoText}</span>
                </div>
            );
        } else {
            return null;
        }
    }

    _renderTotalMeasurementsPaneControl = () => {
        if (this.props.selectedCategory.name !== 'vehicle'
            && this.props.selectedCategory.name !== 'moto'
            && this.props.selectedCategory.name !== 'water_transport'
            && this.props.selectedCategory.name !== 'people'
            && this.props.selectedCategory.name !== 'animals') {
            return (<TotalMeasurementsPaneControl selectedCategory={this.props.selectedCategory} />)
        } else {
            return null;
        }
    }

    render() {
        const allCargos = this._renderAllCargos();
        const addCargoButton = this._renderAddCargoButton(allCargos.size);
        const TotalMeasurementsPaneControl = this._renderTotalMeasurementsPaneControl();

        return (
            <div className="Cargos">
                <div className="Cargos-list">
                    <CargosInfo cargos={this.props.cargos} />
                    <div className="Cargos-body">
                        {allCargos}
                        {addCargoButton}
                    </div>
                </div>
                {TotalMeasurementsPaneControl}
            </div>
        );
    }

}

export default Cargos;
