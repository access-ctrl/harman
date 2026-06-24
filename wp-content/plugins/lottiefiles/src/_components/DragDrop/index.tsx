/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Size } from '@lottiefiles/react-ui-kit';
import clsx from 'clsx';
import React, { createRef, useEffect, useState } from 'react';

import { JsonIcon } from '../../assets/Icons/json-icon';
import { Button } from '../Button';

// Export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {}

export interface DragDropProps {
  bordered?: boolean;
  handleFileChosen: (value: FileList) => void;
  onDrag: (value: DragEvent) => void;
  onDragDrop: (value: FileList) => void;
  onDragIn: (value: DragEvent) => void;
  onDragOut: (value: DragEvent) => void;
}

export const DragDrop: React.FC<DragDropProps> = (props: DragDropProps) => {
  const [dragging, setIsDragging] = useState(false);
  const { bordered, handleFileChosen, onDrag, onDragDrop, onDragIn, onDragOut } = props;
  const dropRef = createRef<HTMLDivElement>();
  const inputRef = createRef<HTMLInputElement>();
  const handleDrag = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    onDrag(e);
  };
  const handleDragIn = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    onDragIn(e);
    setIsDragging(() => true);
  };
  const handleDragOut = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    onDragOut(e);
  };
  const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();

    if (e.dataTransfer) {
      if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        onDragDrop(e.dataTransfer.files);
        e.dataTransfer.clearData();
      }
    }
  };

  useEffect(() => {
    const div = dropRef.current;

    if (div) {
      div.addEventListener('dragenter', e => handleDragIn(e));
      div.addEventListener('dragleave', e => handleDragOut(e));
      div.addEventListener('dragover', e => handleDrag(e));
      div.addEventListener('drop', e => handleDrop(e));
    }
  }, [dropRef.current]);

  return (
    <div
      className={clsx('lf-_lf-drag-box', bordered ? 'lf-bordered' : '', dragging ? 'lf-bg-teal-100' : '')}
      ref={dropRef}
    >
      <input
        ref={inputRef}
        type="file"
        onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
          if (e.target.files) {
            handleFileChosen(e.target.files);
          }
        }}
        hidden
      />
      <div className="lf-flex lf-flex-col lf-items-center lf-text-center">
        <JsonIcon />

        <p className="lf-label lf-text-bold lf-lg lf-mt-6">Drop animation here</p>
        <p className="lf-label lf-sm lf-mb-4">or</p>
        <Button appearance={Appearance.primary} size={Size.small} onClick={() => inputRef.current?.click()}>
          Browse
        </Button>
      </div>
    </div>
  );
};
export default DragDrop;
