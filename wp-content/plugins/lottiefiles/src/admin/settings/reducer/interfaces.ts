/**
 * Copyright 2022 Design Barn Inc.
 */

import { SettingsKind } from './enum';

export interface IStateProps {
  error?: string;
  isLoading?: boolean;
  settings?: IHNResponseProps;
}

export interface IUserDataProps {
  accessToken: string;
  avatarUrl: string;
  email: string;
  id: number;
  name: string;
  username: string;
}

export interface IHNResponseProps {
  copyLottieToMedia?: boolean;
  isAdmin?: boolean;
  shareUserData?: boolean;
  shareWithOthers?: boolean;
  switchAccount: boolean;
  enableCdn?: boolean;
  userData?: IUserDataProps;
}

export interface IActionProps {
  results: IStateProps;
  type: SettingsKind;
}

export interface ISettingReducerProps {
  (state: IStateProps, action: IActionProps): IStateProps;
}

export interface IActionsTypes {
  (results: IStateProps): IActionProps;
}
