import { Text, View, type ViewProps } from 'react-native';

export type PacoLogoVariant = 'paco' | 'paco-app';

export type PacoLogoProps = ViewProps & {
  variant?: PacoLogoVariant;
  size?: 'sm' | 'md' | 'lg';
};

const sizeMap = { sm: 14, md: 20, lg: 28 } as const;

/**
 * Placeholder de marca hasta integrar SVG/PNG del manual.
 * Variantes: Paco (wordmark corto) y Paco App (subtítulo app).
 */
export function PacoLogo({ variant = 'paco', size = 'md', style, ...rest }: PacoLogoProps) {
  const fs = sizeMap[size];
  const isApp = variant === 'paco-app';
  return (
    <View className="flex-row items-baseline" style={style} {...rest}>
      <Text
        className="font-gordita text-paco-blue dark:text-gray-niebla"
        style={{ fontSize: fs, lineHeight: fs * 1.2 }}
      >
        Paco
      </Text>
      {isApp ? (
        <Text
          className="ml-1 font-lato-bold text-paco-accent"
          style={{ fontSize: fs * 0.55, lineHeight: fs * 0.66 }}
        >
          {' '}
          App
        </Text>
      ) : null}
    </View>
  );
}
