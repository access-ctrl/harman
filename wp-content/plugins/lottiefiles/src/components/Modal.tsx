/**
 * Copyright 2022 Design Barn Inc.
 */

import { Modal } from '@wordpress/components';
import * as React from 'react';
import styled from 'styled-components';

import { IBigModelProps } from '../interfaces';

const StyledModal = styled(Modal)`
  width: 90% !important;
  font-family: averta_stdregular, sans-serif;
  min-height: 90%;
  .components-modal__header {
    display: none;
  }
  .components-modal__content {
    margin-top: 0px;
    padding: 0px;
    :before {
      display: none;
    }
  }
`;

export const BigModal: React.FC<IBigModelProps> = ({ children, toggleModal }: IBigModelProps) => {
  return (
    <StyledModal id="lf-wordpress" title="" onRequestClose={(): void => toggleModal(false)}>
      {children}
    </StyledModal>
  );
};
