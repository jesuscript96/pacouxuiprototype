import { Text, View, type ViewProps } from 'react-native';

export type PacoAvatarVariant = 'paco' | 'paco-app';

export type PacoAvatarProps = ViewProps & {
  variant?: PacoAvatarVariant;
  size?: number;
  label?: string;
};

/**
 * Avatar circular con inicial de marca (sustituir por ilustraciùn del manual si aplica).
 */
export function PacoAvatar({
  variant = 'paco',
  size = 48,
  label,
  style,
  ...rest
}: PacoAvatarProps) {
  const letter = label ?? (variant === 'paco-app' ? 'P' : 'P');
  const fontSize = size * 0.42;
  return (
    <View
      className="items-center justify-center rounded-full bg-paco-blue dark:bg-gray-pizarra"
      style={[{ width: size, height: size }, style]}
      {...rest}
    >
      <Text
        className="font-gordita text-white"
        style={{ fontSize, lineHeight: fontSize * 1.2 }}
      >
        {letter}
      </Text>
    </View>
  );
}
