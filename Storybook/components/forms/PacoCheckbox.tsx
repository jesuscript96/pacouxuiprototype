import { Pressable, Text, View, type PressableProps } from 'react-native';
import { Check } from 'phosphor-react-native';
import { pacoColors } from '../../constants/theme';

export type PacoCheckboxProps = Omit<PressableProps, 'children'> & {
  label: string;
  checked: boolean;
};

export function PacoCheckbox({ label, checked, className, ...rest }: PacoCheckboxProps) {
  return (
    <Pressable
      accessibilityRole="checkbox"
      accessibilityState={{ checked }}
      className={`flex-row items-center gap-3 py-2 ${className ?? ''}`}
      {...rest}
    >
      <View
        className={`h-6 w-6 items-center justify-center rounded-md border-2 ${
          checked ? 'border-paco-accent bg-paco-accent' : 'border-paco-blue bg-transparent dark:border-paco-blue-soft'
        }`}
      >
        {checked ? <Check color={pacoColors.white} size={16} weight="bold" /> : null}
      </View>
      <Text className="flex-1 font-lato text-base text-gray-carbon dark:text-gray-niebla" style={{ lineHeight: 16 * 1.5 }}>
        {label}
      </Text>
    </Pressable>
  );
}
