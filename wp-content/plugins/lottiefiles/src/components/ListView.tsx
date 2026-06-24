/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';

import { ITileProps, Tile } from './Tile';

export interface IListProps {
  isLoading: boolean;
  list: ITileProps[];
}

export const ListView: React.FC<IListProps> = ({ isLoading, list }: IListProps) => {
  if (isLoading) {
    return <div className="lf-spinner medium bg"></div>;
  }

  return (
    <div
      style={{
        gridAutoRows: '1fr',
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
        gridGap: '40px',
        alignItems: 'stretch',
      }}
    >
      {list.map((item: unknown) => {
        const node = item.node;

        return (
          <Tile
            key={node.id}
            id={node.id}
            lottieUrl={node.lottieUrl}
            bgColor={node.bgColor}
            createdBy={node.createdBy}
          />
        );
      })}
    </div>
  );
};
