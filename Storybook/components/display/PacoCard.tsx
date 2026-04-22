import type { ReactNode } from 'react';
import { Text, View, type ViewProps } from 'react-native';

export type PacoCardProps = ViewProps & {
  title?: string;
  subtitle?: string;
  children?: ReactNode;
};

export function PacoCard({ title, subtitle, children, className, ...rest }: PacoCardProps) {
  return (
    <View
      className={`rounded-2xl border border-gray-humo bg-white p-4 dark:border-gray-pizarra dark:bg-gray-carbon ${className ?? ''}`}
      {...rest}
    >
      {title ? (
        <Text className="font-gordita text-lg text-paco-blue dark:text-paco-blue-soft" style={{ lineHeight: 22 * 1.2 }}>
          {title}
        </Text>
      ) : null}
      {subtitle ? (
        <Text className="mt-1 font-lato text-sm text-gray-pizarra dark:text-gray-humo" style={{ lineHeight: 14 * 1.5 }}>
          {subtitle}
        </Text>
      ) : null}
      {children ? <View className={title || subtitle ? 'mt-3' : ''}>{children}</View> : null}
    </View>
  );
}
