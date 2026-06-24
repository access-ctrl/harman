/**
 * Copyright 2022 Design Barn Inc.
 */

import { IHNResponseProps } from '../reducer';

export interface IAPIProps {
  deleteSettings(data: unknown): Promise<boolean>;
  getSettings(): Promise<IHNResponseProps | boolean | IErrorProps>;
  updateSettings(data: unknown): Promise<IHNResponseProps>;
}

export interface ISettingsProps {
  onDeleteSettings(data: unknown): Promise<boolean>;
  settings: IHNResponseProps;
  updateSettings(): Promise<boolean>;
}

export interface ISetupProps {
  onSaveSettings(data: unknown): Promise<boolean>;
  settings: IHNResponseProps;
}

export interface IErrorProps {
  code: string;
  message: string;
}
