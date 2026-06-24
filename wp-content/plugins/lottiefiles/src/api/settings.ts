/**
 * Copyright 2022 Design Barn Inc.
 */

import apiFetch from '@wordpress/api-fetch';

import { IErrorProps } from '../admin/settings/interfaces';
import { IHNResponseProps } from '../admin/settings/reducer';

const path: string = '/lottiefiles/v1/settings/';

// Fetch settings via the REST API endpoint
export function getSettings() {
  return new Promise((resolve, reject) => {
    apiFetch({
      path,
      method: 'GET',
    }).then(data => {
      resolve(data);
    }).catch(err => {
      reject({ ...err, error: true });
    });
  });
}

// Update settings via the REST API endpoint
export function updateSettings(data: unknown) {
  return new Promise((resolve, reject) => {
    apiFetch({
      path,
      data,
      method: 'POST',
    }).then(updatedData => {
      resolve(updatedData);
    }).catch(err => {
      reject({ ...err, error: true });
    });
  });
}

// Delete settings via the REST API endpoint
export function deleteSettings(data: unknown) {
  return new Promise((resolve, reject) => {
    apiFetch({
      path,
      data,
      method: 'DELETE',
    }).then(() => {
      resolve(true);
    }).catch(err => {
      reject({ ...err, error: true });
    });
  });
}
