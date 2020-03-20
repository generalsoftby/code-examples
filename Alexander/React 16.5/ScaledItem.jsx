import React, { Component } from 'react';
​
import PinchToZoom from '@/components/scale/PinchScale';
​
class ScaledItem extends Component {
    constructor(props) {
        super(props);
        this.state = {
            backgroundPosition: '0% 0%',
            innerWidth: window.innerWidth,
        };
    }
​
    componentWillUnmount() {
        window.removeEventListener('resize', this.handleResize);
    }
​
    componentDidMount() {
        window.addEventListener('resize', this.handleResize);
    }

    handleResize = () => {
        this.setState({
            innerWidth: window.innerWidth,
        });
    };
​
    handleMouseMove = (e) => {
        const {
            left,
            top,
            width,
            height,
        } = e.target.getBoundingClientRect();
        const x = (e.pageX - left) / width * 100;
        const y = (e.clientY - top) / height * 100;
        this.setState({ backgroundPosition: `${x}% ${y}%` })
    };
​
    render() {
        const width = window.innerWidth;
        const height = window.innerHeight;
        return (
            <div>
                {this.state.innerWidth >= 1025
                    ? <figure
                        onMouseMove={this.handleMouseMove}
                        style={{
                            backgroundPosition: this.state.backgroundPosition,
                            backgroundImage: `url(${ this.props.img.image })`,
                            backgroundSize: this.props.img.ratio ? `${this.props.img.ratio * 100}%` : '300%',
                        }}
                    >
                        {<img src={this.props.img.image} alt=""/>}
                    </figure>
                    : <PinchToZoom width={width} height={height}>
                        {(x, y, scale) => (
                            <img
                                src={this.props.img.image}
                                style={{
                                    pointerEvents: scale === 1 ? 'auto' : 'none',
                                    transform: `translate3d(${x}px, ${y}px, 0) scale(${scale})`,
                                    transformOrigin: '0 0',
                                }} />
                        )}
                    </PinchToZoom>
                }
                <img className="instead-figure" src={this.props.img.image} alt=""/>
            </div>
        );
    }
}
​
export default ScaledItem;
