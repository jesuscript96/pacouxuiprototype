import { Text, TextInput, View, type TextInputProps } from 'react-native';

export type PacoTextFieldProps = TextInputProps & {
  label: string;
  errorMessage?: string;
  hint?: string;
};

export function PacoTextField({
  label,
  errorMessage,
  hint,
  className,
  editable = true,
  ...rest
}: PacoTextFieldProps) {
  const hasError = Boolean(errorMessage);
  return (
    <View className="w-full">
      <Text
        className="mb-1.5 font-lato-bold text-sm text-paco-blue dark:text-paco-blue-soft"
        style={{ lineHeight: 14 * 1.5 }}
      >
        {label}
      </Text>
      <TextInput
        editable={editable}
        placeholderTextColor="#5c6488"
        className={`rounded-xl border-2 bg-white px-4 py-3 font-lato text-base text-gray-carbon dark:border-gray-pizarra dark:bg-gray-carbon dark:text-gray-niebla ${
          hasError ? 'border-emphasis-red' : 'border-gray-humo dark:border-gray-pizarra'
        } ${!editable ? 'opacity-60' : ''} ${className ?? ''}`}
        style={{ lineHeight: 20 * 1.5 }}
        {...rest}
      />
      {hint && !hasError ? (
        <Text className="mt-1 font-lato text-xs text-gray-pizarra dark:text-gray-humo" style={{ lineHeight: 14 * 1.5 }}>
          {hint}
        </Text>
      ) : null}
      {hasError ? (
        <Text className="mt-1 font-lato text-xs text-emphasis-red" style={{ lineHeight: 14 * 1.5 }}>
          {errorMessage}
        </Text>
      ) : null}
    </View>
  );
}
