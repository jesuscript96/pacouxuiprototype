import { ActivityIndicator, Pressable, Text, type PressableProps } from 'react-native';

export type PacoButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost';

export type PacoButtonProps = Omit<PressableProps, 'children'> & {
  children: string;
  variant?: PacoButtonVariant;
  loading?: boolean;
};

export function PacoButton({
  children,
  variant = 'primary',
  loading = false,
  disabled,
  className,
  ...rest
}: PacoButtonProps) {
  const base =
    'rounded-2xl px-5 py-3 items-center justify-center min-h-[48px] active:opacity-90';
  const variants: Record<PacoButtonVariant, string> = {
    primary: 'bg-paco-accent',
    secondary: 'bg-paco-blue-deep',
    outline: 'border-2 border-paco-accent bg-transparent',
    ghost: 'bg-transparent',
  };
  const textVariants: Record<PacoButtonVariant, string> = {
    primary: 'text-white',
    secondary: 'text-white',
    outline: 'text-paco-accent',
    ghost: 'text-paco-blue dark:text-gray-niebla',
  };
  const merged = `${base} ${variants[variant]} ${disabled || loading ? 'opacity-50' : ''} ${className ?? ''}`;

  return (
    <Pressable accessibilityRole="button" disabled={disabled || loading} className={merged} {...rest}>
      {loading ? (
        <ActivityIndicator color={variant === 'outline' || variant === 'ghost' ? '#fb4f33' : '#FFFFFF'} />
      ) : (
        <Text
          className={`font-gordita ${textVariants[variant]}`}
          style={{ fontSize: 16, lineHeight: 16 * 1.2 }}
        >
          {children}
        </Text>
      )}
    </Pressable>
  );
}
