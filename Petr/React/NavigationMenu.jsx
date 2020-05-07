class NavigationMenu extends Component {
    constructor(props) {
        super(props);

        this.state = {
            searchDisplayed:false
        };
    }

    toggleSearchPanel = () => {
        this.setState({
            searchDisplayed: !this.state.searchDisplayed
        })
    };

    handleCopyEmail = () => {
        const alertKey = uuid();

        this.props.showAlert({
            text: 'Copied!',
            type: 'success',
            id: alertKey
        });

        setTimeout(()=>{
            this.props.hideAlert(alertKey)
        },5000);
    }

    render() {
        let props = this.props;

        let phone = props.emailAndPhone.phone;
        let email = props.emailAndPhone.email;

        return (
            <React.Fragment>
                <div>
                    <ul className={"nav-links row" + (props.showMore ? ' open' : '')}>
                        {props.menus.map(function(item, i) {
                            return (item.parent_id == null &&
                                <NavigationMenuItem
                                    first
                                    item={item}
                                    id={item.id}
                                    key={i}
                                    onChangeMenu={props.changeVisibleMenu}
                                    currentOpenMenu={props.openMenu}
                                    innerWidth={props.innerWidth}
                                    history = {props.history}
                                />
                            );
                        })}

                        <li className="leave-request unselectable" onClick={props.handleToggleRequestModal}>Оставить заявку</li>

                        {this.state.searchDisplayed ?
                            <>
                                <SearchComponent toggleSearchPanel = {this.toggleSearchPanel}/>
                            </>
                            : (
                                <>
                                    {props.emailAndPhone.phone && (
                                        <li>
                                            <a href={"tel:"+phone}>{props.emailAndPhone.phone}
                                            </a>
                                        </li>
                                    )}

                                    {props.emailAndPhone.email  && (
                                        <li>
                                            <div className='nav-item-email'>
                                                <a href={"mailto:"+email}>{props.emailAndPhone.email}</a>
                                                <div className='copy-email unselectable'>
                                                    <CopyToClipboard
                                                        text={props.emailAndPhone.email}
                                                        onCopy={this.handleCopyEmail}>
                                                        <div className='d-flex'>
                                                            <ReactSVG className='copy-email__image' src="/assets/icons/copy.svg"/>
                                                            <div className="copy-email__text">
                                                                Copy email
                                                            </div>
                                                        </div>
                                                    </CopyToClipboard>

                                                </div>
                                            </div>
                                        </li>
                                    )}
                                    {
                                        props.isSearchDisplay  &&
                                        <SearchWidget
                                            toggleSearchPanel = {this.toggleSearchPanel}
                                        />
                                    }
                                </>
                            )
                        }

                        {
                            this.props.showAuthAndCartPanel &&
                            <Fade unmountOnExit = {true}
                                  mountOnEnter = {true}
                                  right
                                  when={this.props.sticky ? this.props.sticky : false}
                                  timeout={500}
                            >
                                <div className ={this.props.sticky ? 'my' : ''}>
                                    <LoginWidget
                                        isAuthorized = {this.props.isAuthorized}
                                        iconOnly = {true}
                                    />

                                    <CartWidget
                                        iconOnly = {true}
                                        cart = {this.props.cart}
                                    />
                                </div>
                            </Fade>
                        }
                    </ul>

                    {props.showMore && <span className='overlay'></span>}
                </div>
            </React.Fragment>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        showAlert: data => dispatch(showAlert(data)),
        hideAlert: id => dispatch(hideAlert(id)),
    };
}

function mapStateToProps(state) {
    return {
        showAuthAndCartPanel: state.header.showAuthAndCartPanel,
    }
}

export default connect(mapStateToProps,mapDispatchToProps)(NavigationMenuL);