/**
 * Copyright 2022 Design Barn Inc.
 */

export const debounce = <Params extends unknown[]>(
  func: (...args: Params) => unknown,
  timeout: number,
): ((...args: Params) => void) => {
  let timer: NodeJS.Timeout;

  return (...args: Params): void => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func(...args);
    }, timeout);
  };
};
