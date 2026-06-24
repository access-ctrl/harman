/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';
import styled from 'styled-components';

import { color } from '../assets/colors';

import { Label } from './Label';

interface IInputProps {
  className?: string;
  disabled?: boolean;
  label?: string;
  max?: string;
  min?: string;
  onChange: (color: string) => void;
  placeholder?: string;
  type?: string;
  value?: string;
}

export const Input = styled.input`
  display: block;
  width: 100%;
  height: 35px;
  padding: 0.375rem 0.75rem;
  line-height: 1.5;
  background-color: ${color.white};
  background-clip: padding-box;
  appearance: none;
  border-radius: 0.25rem;
  border: 1px solid #757575;
`;

export const InputControl = styled.div`
  width: 100%;
  margin-bottom: 10px;
  label {
    padding-bottom: 8px;
  }
`;

export const InputLabel: React.FC<IInputProps> = ({
  className,
  disabled,
  label,
  onChange,
  placeholder,
  type,
  value,
  ...rest
}: IInputProps) => {
  return (
    <InputControl className={className}>
      <Label>{label}</Label>
      <Input
        type={type}
        placeholder={placeholder}
        value={value}
        disabled={disabled}
        onChange={(event: React.ChangeEvent<HTMLInputElement>): void => onChange(event.target.value)}
        {...rest}
      />
    </InputControl>
  );
};
