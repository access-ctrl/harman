/**
 * Copyright 2022 Design Barn Inc.
 */

import { Collapsible } from '@lottiefiles/react-ui-kit';
import filesize from 'filesize';
import { AnimationItem } from 'lottie-web';
import * as React from 'react';

enum PlayerState {
  Error = 'error',
  Frozen = 'frozen',
  Loading = 'loading',
  Paused = 'paused',
  Playing = 'playing',
  Stopped = 'stopped',
}

interface IRenderGraphProps {
  animationData?: unknown;
  debug?: boolean;
  height?: number;
  instance?: AnimationItem;
  loop?: boolean;
  pause?: () => void;
  play?: () => void;
  playerState?: PlayerState;
  seeker?: number;
  setLoop?: (value: boolean) => void;
  setSeeker?: (seek: number) => void;
  stop?: () => void;
  type: 'bars' | 'lines';
  visible?: boolean;
  width?: number;
}

export class RenderGraph extends React.Component<IRenderGraphProps> {
  private canvas: HTMLCanvasElement | null = null;

  private frameStartTime = 0;

  private currentFrame = 1;

  private values: number[] = [];

  private canvasContext: CanvasRenderingContext2D | null = null;

  private height = 1;

  private width = 1;

  public componentDidMount() {
    const { instance } = this.props;

    if (this.canvas) {
      const { height, width } = this.canvas.getBoundingClientRect();

      this.canvasContext = this.canvas.getContext('2d');
      this.height = height || 150;
      this.width = width || 350;

      if (instance) {
        this.createGraph(instance);
      }
    }
  }

  public componentWillReceiveProps(nextProps: Readonly<IRenderGraphProps>) {
    if (nextProps.instance && nextProps.instance !== this.props.instance) {
      this.createGraph(nextProps.instance);
    }
  }

  public render() {
    const { animationData, debug, height, width } = this.props;

    this.renderGraph();

    return (
      <div className="p-4">
        <Collapsible
          open={false}
          fullWidthContent={false}
          title={
            <div style={{ display: 'lf-flex', alignItems: 'center' }}>
              {/* <RenderGraphIcon */}
              {/* // TODO */}
              {/* // style={{ marginRight: '10px' }} */}
              {/* />{' '} */}
              Render Graph
            </div>
          }
          actions={<span style={{ color: '#fff' }}>{filesize(JSON.stringify(animationData).length)}</span>}
        >
          <div style={{ display: !debug ? 'none' : 'block' }}>
            <canvas ref={el => (this.canvas = el)} width={width} height={height} />
            <div style={{ marginLeft: '20px' }}>
              Size: {animationData && animationData.w} x {animationData && animationData.h}
            </div>
            <div style={{ marginLeft: '20px' }}>Layers: {animationData && animationData.layers.length}</div>
          </div>
        </Collapsible>
      </div>
    );
  }

  private createGraph(instance: AnimationItem) {
    if (!instance) {
      return;
    }

    // Attach event listener for frame event
    instance.addEventListener('enterFrame', () => {
      if (!this.frameStartTime) {
        this.frameStartTime = performance.now();
        // @ts-ignore
        this.currentFrame = Math.floor(Number.parseInt(instance.currentFrame, 10));
      } else {
        const frameEndTime = performance.now();

        // @ts-ignore
        this.currentFrame = Math.floor(Number.parseInt(instance.currentFrame, 10));
        this.values[this.currentFrame] = Number(frameEndTime - this.frameStartTime);
        this.frameStartTime = frameEndTime;
      }
    });
  }

  private renderGraph() {
    const { playerState, type } = this.props;

    if (playerState === PlayerState.Playing && this.canvasContext) {
      const xOffset = 20;
      const yOffset = this.height - 20;
      const xSpacing = (this.width - 20) / this.values.length;

      // Const max = Math.max(...this.values);
      const scalingFactor = this.height / 100;

      // Clear canvas
      this.canvasContext.clearRect(0, 0, this.width, this.height);

      // Draw y axis
      this.drawLine(this.canvasContext, xOffset, 1, xOffset, yOffset, 'black');

      // Draw y axis ticks and labels
      // const yLabelCount = 10;
      const yLabelFrequency = 50;

      for (let y = 0; y < yOffset; y += yLabelFrequency) {
        // Draw ticks
        this.drawLine(this.canvasContext, xOffset - 5, yOffset - y, xOffset, yOffset - y, 'black');
        this.canvasContext.font = '8px sans-serif';
        this.canvasContext.fillStyle = 'gray';
        this.canvasContext.fillText(y.toString(), xOffset - 20, yOffset - y);
      }

      // Draw x axis
      this.drawLine(this.canvasContext, xOffset, yOffset, this.width, yOffset, 'black');

      // Draw x axis ticks and labels
      // const xLabelCount = 5;
      // const xLabelFrequency = this.values.length / xLabelCount;
      // for (let x = 0; x < this._values.length; x += xLabelFrequency) {
      //     // Draw ticks
      //     // drawLine(this.canvasContext, (x + 1) * xSpacing + xOffset, yOffset + 5, (x + 1) * xSpacing + xOffset, yOffset, 'black');
      //     // this.canvasContext.font = '8px sans-serif';
      //     // this.canvasContext.fillStyle = 'gray';
      //     // this.canvasContext.fillText(x, (x + 1) * xSpacing + xOffset, yOffset + 15);
      // }

      for (let i = 0; i < this.values.length; i++) {
        let x1 = 0;
        let x2 = 0;
        let y1 = 0;
        let y2 = 0;

        if (type === 'bars') {
          x1 = i + 1;
          y1 = 0;
          x2 = i + 1;
          y2 = this.values[i];
        } else {
          x1 = i;
          y1 = this.values[i - 1];
          x2 = i + 1;
          y2 = this.values[i];
        }

        // Let scalingFactor = ((this._values[i] * (this.height / max))).toFixed(2);

        // if (Number.isNaN(scalingFactor)) {
        //     scalingFactor = 1;
        // }

        this.drawLine(
          this.canvasContext,
          x1 * xSpacing + xOffset,
          yOffset - y1 * scalingFactor,
          x2 * xSpacing + xOffset,
          yOffset - y2 * scalingFactor,
          this.currentFrame === i ? 'red' : 'gray',
        );
      }
    }
  }

  private drawLine(context: CanvasRenderingContext2D, sX: number, sY: number, dX: number, dY: number, color: string) {
    if (color) {
      context.strokeStyle = color;
    }

    context.lineWidth = 1;

    context.beginPath();
    context.moveTo(sX + 0.5, sY + 0.5);
    context.lineTo(dX + 0.5, dY + 0.5);
    context.stroke();
  }
}
