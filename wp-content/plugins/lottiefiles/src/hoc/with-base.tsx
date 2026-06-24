/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';

import { Sidebar } from '../gutenberg-block/Layout';
import { IHostAppProps } from '../interfaces';

interface IBaseProps {
  SidebarContent: React.FC<P>;
}

export const withBase =
  (props: IBaseProps) =>
  (WrappedComponent: React.FC<P>): React.FC<P> => {
    const { SidebarContent } = props;
    const Base: React.FC<IHostAppProps> = ({ attributes, setAttributes }: IHostAppProps): JSX.Element => {
      return (
        <div
          style={{
            display: 'flex',
            height: 'calc(100% - 80px)',
            minHeight: '600px',
          }}
        >
          <Sidebar>{<SidebarContent />}</Sidebar>
          <div className="lf-flex-grow p-1 lf-bg-contentBg lf-overflow-auto lf-pb-0">
            <WrappedComponent attributes={attributes} setAttributes={setAttributes} />
          </div>
        </div>
      );
    };

    return Base;
  };
