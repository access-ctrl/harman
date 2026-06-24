/**
 * Copyright 2022 Design Barn Inc.
 */

export class PlayerErrorBoundary extends React.Component {
  state = {
    hasError: false,
  };

  componentDidCatch(error: unknown) {
    this.setState({ hasError: false });
  }

  static getDerivedStateFromError(error: unknown) {
    return {
      hasError: true,
    };
  }

  render() {
    return this.state.hasError ? <div>There was an error...</div> : this.props.children;
  }
}
