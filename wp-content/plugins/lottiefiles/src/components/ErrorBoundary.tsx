/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';

import { ErrorBoundaryFallback } from './ErrorBoundaryFallback';

export interface ErrorInfo {
  componentStack: string;
}

export interface ErrorBoundaryProps {
  readonly children: React.ReactNode;
  onError?: (error: Error, componentStack: string) => void;
}

export interface ErrorBoundaryState {
  error: Error | null;
  errorInfo: ErrorInfo | null;
}

export class ErrorBoundary extends React.Component<ErrorBoundaryProps, ErrorBoundaryState> {
  // eslint-disable-next-line @typescript-eslint/explicit-member-accessibility
  state: ErrorBoundaryState = {
    error: null,
    errorInfo: null,
  };

  // eslint-disable-next-line @typescript-eslint/explicit-member-accessibility
  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { error, errorInfo: null };
  }

  // eslint-disable-next-line @typescript-eslint/explicit-member-accessibility
  componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    const { onError } = this.props;

    if (typeof onError === 'function') {
      try {
        onError.call(this, error, errorInfo.componentStack);
      } catch (_) {
        /** */
      }
    }

    this.setState({ errorInfo });
  }

  // eslint-disable-next-line @typescript-eslint/explicit-member-accessibility
  render(): React.ReactNode {
    const { children } = this.props;
    const { error, errorInfo } = this.state;

    if (error !== null) {
      return <ErrorBoundaryFallback error={error} stack={errorInfo?.componentStack || ''} />;
    }

    return children;
  }
}
