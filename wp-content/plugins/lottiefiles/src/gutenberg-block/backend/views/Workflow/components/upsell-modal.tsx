/**
 * Copyright 2020 Design Barn Inc.
 */

import { Appearance, Size } from '@lottiefiles/react-ui-kit';
import * as React from 'react';

import { Button } from '../../../../../_components';
import { CheckIcon, CloseIcon } from '../../../../../assets/Icons';
import upsellImage from '../../../../../assets/images/upsell.png';
// eslint-disable-next-line import/no-cycle
import { useWorkspace } from '../../../../../context/workspace-provider';
import { URLS } from '../../../../../helpers/consts';

interface UpsellModalProps {
  closeModal: () => void;
}

const benefits = [
  'Unlimited projects, files and previews',
  'Lottie Editor',
  'Lottie Color Palette',
  'File size optimization',
  'Export Lotties to GIF and MP4',
  'Private sharing and commenting',
  'Custom embeds',
  'Track version history',
];

export const UpsellModal: React.FC<UpsellModalProps> = ({ closeModal }: UpsellModalProps) => {
  const { isClosed } = useWorkspace();

  return (
    <>
      {!isClosed && (
        <div
          style={{ background: 'rgba(0, 0, 0, 0.4)', zIndex: 20 }}
          className="absolute top-0 bottom-0 right-0 left-0 flex items-center justify-center overflow-auto"
        >
          <div id="modal-card" className="lf-relative lf-w-2/3  lf-shadow-xl ">
            <button onClick={closeModal} className="lf-absolute lf-top-3 lf-right-3">
              <CloseIcon className="lf-text-gray-600 lf-fill-current h-5 w-5" />
            </button>
            <div className=" lf-flex lf-flex-row lf-p-8 lf-bg-white lf-rounded-lg ">
              <div className="lf-flex lf-flex-col lf-gap-2 lf-w-2/5 lf-p-5">
                <h3 className=" lf-text-lg lf-text-black lf-font-bold">Do more with an individual plan</h3>
                <p className="lf-text-base lf-text-gray-600">
                  Enjoying the LottieFiles platform? Then you’re going to LOVE our Individual plan! Upgrade now to
                  access a whole bunch of features that will truly unleash the power of Lottie.
                </p>
                <ul className="lf-text-gray-600 grid lf-gap-1 lf-pt-1 lf-pb-1.5">
                  <h3>Unlock all these amazing features:</h3>
                  {benefits.map((benefit: string, i: number) => (
                    <li key={i} className="lf-flex lf-gap-2 lf-items-center lf-text-base">
                      <div>
                        <CheckIcon />
                      </div>
                      <span className="">{benefit}</span>
                    </li>
                  ))}
                </ul>

                <Button
                  onClick={() => {
                    window.open(URLS.upsell, '_blank');
                    closeModal();
                  }}
                  appearance={Appearance.primary}
                  size={Size.fluid}
                >
                  Upgrade now
                </Button>
                <p className="lf-text-center">Only $25/month</p>
              </div>
              <img
                src={upsellImage}
                className=" lf-rounded-lg lf-w-3/5 lf-object-cover lf-border-0 "
                style={{ minHeight: '550px' }}
              />
            </div>
          </div>
        </div>
      )}
    </>
  );
};
