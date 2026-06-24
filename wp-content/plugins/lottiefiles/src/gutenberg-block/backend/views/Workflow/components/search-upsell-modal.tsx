/**
 * Copyright 2020 Design Barn Inc.
 */

import { Appearance, Button, Size } from '@lottiefiles/react-ui-kit';
import * as React from 'react';

import { CheckIcon, CrownBig } from '../../../../../assets/Icons';
import upsellImage from '../../../../../assets/images/upsell.png';
import { URLS } from '../../../../../helpers/consts';

interface UpsellModalProps {
  closeModal: () => void;
  isClosed: boolean;
}

const benefits = [
  'Unlimited projects, files and previews',
  'Lottie Editor',
  'Lottie Color Palette',
  'File size optimization',
  'and more...',
];

export const SearchUpsellModal: React.FC<UpsellModalProps> = ({ isClosed }: UpsellModalProps) => {
  return (
    <>
      {!isClosed && (
        <div
          style={{
            zIndex: 20,
            background: 'linear-gradient(180deg, rgba(35, 35, 35, 0) 0.39%, #fff 10.54%)',
          }}
          className="absolute top-0 bottom-0 right-0 left-0 flex items-center justify-center overflow-auto"
        >
          <div className="h-full pt-8">
            <div id="modal-card" className="rounded-lg max-w-sm p-8">
              <div className="flex flex-col items-center gap-3 pb-3">
                <CrownBig />
                <h3 className="text-center text-md font-semibold">
                  Upgrade to search for assets in your workspace and unlock amazing features
                </h3>
              </div>
              {/* TODO: Fix this upsell image */}
              <img src={upsellImage} className="w-full h-full rounded-lg lf-border-0" />
              <div className="rounded-b-lg p-5 flex flex-col gap-2">
                <ul className="lf-text-gray-700 grid gap-1 text-sm pb-1.5">
                  {benefits.map((benefit: string, i: number) => (
                    <li key={i} className="flex gap-2 items-center">
                      <div>
                        <CheckIcon />
                      </div>
                      <span className="text-xs">{benefit}</span>
                    </li>
                  ))}
                </ul>

                <Button
                  onClick={() => {
                    window.open(URLS.upsell, '_blank');
                  }}
                  size={Size.fluid}
                  appearance={Appearance.primary}
                >
                  <span className="text-xs"> Upgrade now</span>
                </Button>
                <div className="text-center text-gray-200 text-xs">Only $25/month</div>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};
