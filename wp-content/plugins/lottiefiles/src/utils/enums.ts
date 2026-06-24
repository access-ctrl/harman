/**
 * Copyright 2022 Design Barn Inc.
 */

export const CURSOR_TYPES = [
  { value: 'none', label: 'None' },
  { value: 'click', label: 'Click' },
  { value: 'toggle', label: 'Toggle' },
  { value: 'hold', label: 'Hold' },
  { value: 'pauseHold', label: 'Pause Hold' },
];

export const SCROLL_TYPES = [
  { value: 'none', label: 'None' },
  { value: 'seek', label: 'Seek' },
  { value: 'stop', label: 'Stop' },
  { value: 'loop', label: 'Loop' },
  { value: 'play', label: 'Play' },
];

export enum Mode {
  CURSOR = 'cursor',
  NONE = 'none',
  SCROLL = 'scroll',
}
