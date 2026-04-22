import { Pressable, Text, type PressableProps } from 'react-native';

export type PacoChipProps = Omit<PressableProps, 'children'> & {
  children: string;
  selected?: boolean;
};

export function PacoChip({ children, selected = false, className, ...rest }: PacoChipProps) {
  return (
    <Pressable
      accessibilityRole="tab"
      accessibilityState={{ selected }}
      className={`rounded-full border-2 px-4 py-2 ${
        selected
          ? 'border-paco-accent bg-paco-accent'
          : 'border-paco-blue bg-transparent dark:border-paco-blue-soft'
      } ${className ?? ''}`}
      {...rest}
    >
      <Text
        className={`font-gordita text-sm ${selected ? 'text-white' : 'text-paco-blue dark:text-paco-blue-soft'}`}
        style={{ lineHeight: 16 * 1.2 }}
      >
        {children}
      </Text>
    </Pressable>
  );
}
