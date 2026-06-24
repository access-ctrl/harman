/**
 * Copyright 2022 Design Barn Inc.
 */

import { SettingsKind } from './enum';
import { IActionProps, IStateProps } from './interfaces';

export const settingReducer = (state: IStateProps, action: IActionProps): IStateProps => {
  switch (action.type) {
    case SettingsKind.LOADING:
      return { ...state, isLoading: action.results.isLoading };

    case SettingsKind.UPDATE_SETTINGS:
      return { ...state, isLoading: false, settings: action.results.settings };

    case SettingsKind.ERROR:
      return { ...state, isLoading: false, error: action.results.error };

    default:
      return state;
  }
};
