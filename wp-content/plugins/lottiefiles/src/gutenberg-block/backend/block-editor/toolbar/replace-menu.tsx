/**
 * Copyright 2022 Design Barn Inc.
 */

import { Dropdown, MenuGroup, MenuItem, ToolbarGroup } from '@wordpress/components';
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import * as React from 'react';

import { MyMediaUploader } from '../../../../components/media-upload';
import { LottieContext } from '../../../../context/lottie-provider';
import { IHostAppProps, IToolbarButtonProps } from '../../../../interfaces';

import { ToolbarButton } from './toolbar-buton';

export const ReplaceMenu: React.FC<IHostAppProps> = ({ setAttributes }: IHostAppProps) => {
  const { openModal } = useContext(LottieContext);

  return (
    <ToolbarGroup>
      <MyMediaUploader
        onSelect={(src: string | Record<string, unknown>, jsonSrc: string | Record<string, unknown>): void =>
          setAttributes({ src, jsonSrc })
        }
        render={({ open }: { open(): void }): JSX.Element => (
          <Dropdown
            className="my-container-class-name"
            contentClassName="my-popover-content-classname"
            position="bottom right"
            renderToggle={({ isOpen, onToggle }: IToolbarButtonProps): JSX.Element => (
              <ToolbarButton variant="link" onToggle={onToggle} isOpen={isOpen}>
                {__('Replace')}
              </ToolbarButton>
            )}
            renderContent={({ onToggle }: IToolbarButtonProps): JSX.Element => (
              <MenuGroup>
                <MenuItem
                  onClick={(): void => {
                    onToggle();
                    openModal();
                  }}
                >
                  {' '}
                  {__('Explore LottieFiles')}
                </MenuItem>

                <MenuItem
                  onClick={(): void => {
                    onToggle();
                    open();
                  }}
                >
                  {__('Media Library')}
                </MenuItem>

                <MenuItem
                  onClick={(): void => {
                    onToggle();
                    open();
                  }}
                >
                  {__('Upload')}
                </MenuItem>
              </MenuGroup>
            )}
          />
        )}
      />
    </ToolbarGroup>
  );
};
