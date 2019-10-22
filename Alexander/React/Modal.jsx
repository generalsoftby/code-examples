import React, { Component } from 'react';

class Modal extends Component {
    constructor(props) {
        super(props);
        const { discount, discountType } = this.props;
        this.state = {
            discount,
            discountType: discountType || 'value',
        };
    }

    onSubmit = (event) => {
        event.preventDefault();

        const { discount, discountType } = this.state;
        const { priceId } = this.props;
        this.props.changeDiscount(priceId, discount, discountType);
        document.getElementById(`close-button ${priceId}`).click();
    };

    onChange = (event) => {
        this.setState({
            discount: event.target.value,
            discountType: this.state.discountType,
        });
    };

    onSelected = (event) => {
        this.setState({
            discount: this.state.discount,
            discountType: event.target.value,
        });
    };

    render() {
        const classDiscount = `modal discount-modal-specification-${this.props.priceId}`;
        const closeButton = `close-button ${this.props.priceId}`;
        let loyaltySystems = null;

        if (this.props.loyaltySystems) {
            loyaltySystems = this.props.loyaltySystems.map((loyaltySystem) => {
                const createMarkup = function () {
                    return { __html: loyaltySystem.discountsList };
                };
                return (
                    <tr>
                        <td>
                            {loyaltySystem.title}
                        </td>
                        <td>
                            {loyaltySystem.is_discount_amount ? 'Да' : 'Нет'}
                        </td>
                        <td
                            dangerouslySetInnerHTML={createMarkup()}
                        />
                    </tr>
                );
            });
        }

        return (
            <React.Fragment>
                <div className={classDiscount} id="discountModal" tabIndex="-1" role="dialog" >
                    <div className="modal-dialog" role="document">
                        <form
                            className="form form-horizontal modal-content"
                            autocomplete="off"
                            onSubmit={this.onSubmit}
                        >
                            <div className="modal-header">
                                <h4 className="modal-title">Скидка</h4>
                                <button
                                    type="button"
                                    id={closeButton}
                                    className="close"
                                    data-dismiss="modal"
                                    aria-label="Close"
                                >
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div className="modal-body">
                                <div>
                                    <div className="form-group discount-form-group">
                                        <div className="input-group">
                                            <input
                                                className="form-control"
                                                defaultValue={this.props.discount}
                                                onChange={this.onChange}
                                            />
                                            <div className="input-group-append">
                                                <select onChange={this.onSelected} className="discount-type">
                                                    <option
                                                        selected={this.props.discountType === 'value'}
                                                        value="value"
                                                    >
                                                        руб.
                                                    </option>
                                                    <option
                                                        selected={this.props.discountType === 'percentage'}
                                                        value="percentage"
                                                    >
                                                        %
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="form-group">
                                    <label className="col-form-label">Скидки контрагента:</label>
                                    <div className="table-responsive mt-3">
                                        <table className="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th>Название</th>
                                                <th>Суммируется</th>
                                                <th>Значение</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {loyaltySystems || ''}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-default btn-cancel"
                                    data-dismiss="modal"
                                >
                                    Отмена
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-default btn-delete"
                                >
                                    &nbsp;
                                </button>
                                <button
                                    type="submit"
                                    className="btn btn-default btn-ok"
                                >
                                    &nbsp;
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </React.Fragment>
        );
    }
}

export default Modal;
