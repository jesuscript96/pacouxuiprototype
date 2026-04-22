import { View, type ViewProps } from 'react-native';

export type PacoDividerProps = ViewProps & {
  inset?: boolean;
};

export function PacoDivider({ inset = false, className, ...rest }: PacoDividerProps) {
  return (
    <View
      className={`h-px bg-gray-humo dark:bg-gray-pizarra ${inset ? 'ml-4' : ''} ${className ?? ''}`}
      accessibilityRole="none"
      {...rest}
    />
  );
}
