import React from "react";

class Paragraph extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showFullTask: false,
        };
        this.wrapperRef = React.createRef();
    }

    isOverflowed = (el) => {
        const {
            scrollWidth,
            offsetWidth,
            scrollHeight,
            offsetHeight,
        } = el;

        return scrollWidth > offsetWidth || scrollHeight > offsetHeight;
    };

    toggleArrow = () => {
        this.setState({
            showFullTask: !this.state.showFullTask,
        });
    };

    render() {
        const { showFullTask } = this.state;
        const { data } = this.props;

        return (
            <div
                ref={this.wrapperRef}
                className={`task-text ${showFullTask && 'open'}`}
            >
                <div
                    className='overflowed'
                    dangerouslySetInnerHTML={{ __html: data.text }}
                />
                {this.isOverflowed(this.wrapperRef)
                    ? <p
                        className="arrow"
                        onClick={this.toggleArrow}
                    >
                        <span> </span>
                    </p>
                    : ''
                }
            </div>
        );
    }
}

export default Paragraph;