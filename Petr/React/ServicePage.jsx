class ServicePage extends Component {
    constructor(props) {
        super(props);

        this.state = {
            redirect: false,
            showWidget: false,
        };

    }

    initPage = (pathname = null) => {
        let url;
        if (pathname) {
            url = pathname.substring(1)
        } else {
            url = this.props.match.params.service
        }

        const type = this.props.menu['/' + url];

        if (!type) {
            this.setState({
                redirect: true
            });
            return;
        }

        let breadCrumbs = [];

        this.deepSearch(this.props.links, '/' + url, breadCrumbs);

        breadCrumbs.push({name: 'Главная', url: '/'});

        this.props.setServiceBreadCrumbs(breadCrumbs.reverse());

        this.setState({showWidget: false});

        switch (type) {
            case 'SERVICE_PAGES': {
                axios.get('/api/services/service_page/' + url)
                    .then(response => {
                        this.setState({
                            redirect: false
                        });

                        const servicePage = response.data.data;
                        this.props.setServicePage(servicePage);
                    })
                    .catch((err) => {
                        if (err.response) {
                            if (err.response.status === 404) {
                                this.setState({
                                    redirect: true
                                })
                            }
                        }
                    });
                break;
            }
            case 'SERVICE_CATEGORY_PAGES': {
                axios.get('/api/services/category_page/' + url)
                    .then(response => {
                        this.setState({
                            redirect: false,
                            showWidget: true,
                        });

                        const servicePage = response.data.data;
                        this.props.setServicePage(servicePage);
                    })
                    .catch((err) => {
                        if (err.response) {
                            if (err.response.status === 404) {
                                this.setState({
                                    redirect: true
                                })
                            }
                        }
                    });

                axios.post('/api/services/search_service_pages', {
                    id: url
                })
                    .then((res) => {
                        this.props.setWidgetData(res.data.data);
                    });

                break;
            }
        }

    }

    deepSearch(tree, search, breadCrumbs) {
        for (let i = 0; i < tree.length; i++) {
            let el = tree[i];
            if (el.url === search) {
                breadCrumbs.push({
                    name: el.name,
                    url: el.url,
                });
                return true
            } else if (el.children.length && this.deepSearch(el.children, search, breadCrumbs)) {
                breadCrumbs.push({
                    name: el.name,
                    url: el.url,
                });
                return true
            }
        }
        return false
    }

    componentDidMount() {
        this.props.history.listen(({pathname}) => {
            this.initPage(pathname);
        })
    }


    componentDidUpdate(prevProps, prevState, snapshot) {
        if (Object.keys(prevProps.menu).length !== Object.keys(this.props.menu).length) {
            this.initPage()
        }
    }

    render() {
        let page = this.props.page;
        let dataForPageAdditionalNavigation = {};

        if (page && page.text_banner) {
            dataForPageAdditionalNavigation = {
                featuresBottom: page.features_bottom_block,
                questions: page.questions,
                prices: page.table_block,
                recalls: page.recalls,
                description: page.content,
                recommend_block: page.recommend_block
            };
        }

        if (this.state.redirect) {
            return (
                <NotFoundPage/>
            )
        }
        if (!this.props.isLoaded) {
            return (
                <Container className="text-center">
                    <Preloader/>
                </Container>
            )
        }

        return (
            <DocumentMeta  {...page.seo_data}>
                <div>
                    <PageBanner
                        text_banner={page.text_banner}
                        breadCrumbs={this.props.breadCrumbs}
                    />
                    }

                    <React.Fragment>
                        <FeaturesBlockFourItems
                            features={page.features_top_block.features}
                        />

                        {
                            this.state.showWidget &&
                            <WidgetPage pageId={page.id} history={this.props.history}/>
                        }

                        <CalculatorTablesSection page={page}/>

                        {
                            page.form_title && page.form_builder_data &&
                            <FormBuider page={page}> </FormBuider>
                        }

                        {!!this.props.works.length &&
                        <div className="main-calculator-carousel">
                            <CarouselBlock title="Примеры наших работ" slides={this.props.works}/>
                        </div>
                        }

                        <AdditionalNavigation
                            dataForPage={dataForPageAdditionalNavigation}
                        />
                    </React.Fragment>
                    }

                    {!this.props.isReviewsActive &&
                    <FeedbackForm/>
                    }

                </div>
            </DocumentMeta>
        )
    }
}

const mapStateToProps = (state) => {
    return {
        page: state.servicePage.page,
        isLoaded: state.servicePage.isLoaded,
        isReviewsActive: state.applicationInfo.isReviewsActive,
        works: state.servicePage.works,
        menu: state.header.menu,
        links: state.header.links,
        breadCrumbs: state.servicePage.serviceBreadCrumbs
    }
};

const mapDispatchToProps = dispatch => {
    return {
        setServicePage: (data) => {
            dispatch(setServicePage(data));

            if (data.page.use_calculator && data.page.calculator_folder_id) {
                dispatch(getFolder(data.page.calculator_folder_id))
                    .then((res) => {
                        const value = store.getState().calculator.groups[0];
                        dispatch(choiceCalculator(value.id, value.defaultValue))
                            .then(() => {
                                const snapshot = getCurrentCalculatorApiSnapshot();

                                axios.post('/api/calculators/calculate', {
                                    calculator_id: snapshot.currentCalculator,
                                    choosed_data: snapshot
                                })
                                    .then(response => {
                                        dispatch(setCalculations(response.data.data));
                                    })
                            })
                    })

            } else if (data.page.use_calculator && data.page.calculator_id) {
                dispatch(choiceServiceCalculator(data.page.calculator_id))
                    .then((response) => {
                        const snapshot = getCurrentCalculatorApiSnapshot();
                        axios.post('/api/calculators/calculate', {
                            calculator_id: snapshot.currentCalculator,
                            choosed_data: snapshot
                        })
                            .then(response => {
                                dispatch(setCalculations(response.data.data));
                            })
                    })
            }

        },
        setWidgetData: (data) => {
            dispatch(setWidgetData(data));
        },
        setServiceBreadCrumbs: data => {
            dispatch(setServiceBreadCrumbs(data))
        }
    }
};

export default connect(mapStateToProps, mapDispatchToProps)(ServicePage);
