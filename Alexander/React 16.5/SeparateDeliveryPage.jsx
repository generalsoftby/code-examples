import React, { Component } from 'react';

import '@/components/delivery/SeparateDeliveryPage.scss';
import {
    Container,
    Row,
    Col,
} from 'reactstrap';
import { connect } from 'react-redux';
import FeedbackForm from '@/components/feedback/feedbackForm/FeedbackForm';
import DeliveryBlock from '@/components/delivery/delieveryBlock';
import axios from 'axios';
import DocumentMeta from 'react-document-meta';

class SeparateDeliveryPage extends Component {

    constructor(props) {
        super(props);

        this.state = {
            seo: {
                title: '',
                description: '',
            },
        };
    }

    componentDidMount() {
        // loading SEO from the back end
        axios('/api/base/static_page_seo_data/12').then((res) => {
            if (res.data.status === 200) {
                this.setState({ seo: res.data.data });
            }
        });
    }

    render() {
        const { seo } = this.state;

        return (
            <DocumentMeta {...seo}>
                <div className="delivery-container">
                    <Container>
                        <Row>
                            <h1>Доставка в любую точку России</h1>
                        </Row>
                        <Row className="content">
                            <div className="delivery-block-body-wrapper">
                                {this.props.variants
                                && <DeliveryBlock
                                    chosedMark={this.props.delivery.chosedMark}
                                    onlyInformation
                                />
                                }
                            </div>
                        </Row>
                    </Container>
                    <div ref={this.formRef}>
                        <FeedbackForm
                            user={this.props.user}
                        />
                    </div>
                </div>
            </DocumentMeta>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        portfolio: state.portfolio,
        allWorks: state.works,
        variants: state.delivery.variants,
        mapMarks: state.delivery.mapMarks,
        type: state.delivery.type,
        user: state.user,
        delivery: state.delivery,
    };
};

export default connect(mapStateToProps)(SeparateDeliveryPage);
