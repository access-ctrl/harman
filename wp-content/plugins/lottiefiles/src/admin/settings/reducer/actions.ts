/**
 * Copyright 2022 Design Barn Inc.
 */

import { SettingsKind } from './enum';
import { IActionsTypes, IActionProps, IStateProps } from './interfaces';

export const setLoading: IActionsTypes = (_results: IStateProps): IActionProps => ({
  results: _results,
  type: SettingsKind.LOADING,
});

export const onUpdateSettings: IActionsTypes = (_settings: IStateProps): IActionProps => ({
  results: _settings,
  type: SettingsKind.UPDATE_SETTINGS,
});
