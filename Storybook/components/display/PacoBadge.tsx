import { Text, View, type ViewProps } from 'react-native';

export type PacoBadgeTone = 'accent' | 'blue' | 'neutral' | 'success' | 'warning' | 'danger';

export type PacoBadgeProps = ViewProps & {
  children: string;
  tone?: PacoBadgeTone;
};

const toneClass: Record<PacoBadgeTone, string> = {
  accent: 'bg-paco-accent',
  blue: 'bg-paco-blue',
  neutral: 'bg-gray-pizarra',
  success: 'bg-emphasis-green',
  warning: 'bg-emphasis-yellow',
  danger: 'bg-emphasis-red',
};

export function PacoBadge({ children, tone = 'accent', className, ...rest }: PacoBadgeProps) {
  return (
    <View className={`self-start rounded-full px-3 py-1 ${toneClass[tone]} ${className ?? ''}`} {...rest}>
      <Text className="font-lato-bold text-xs uppercase tracking-wide text-white" style={{ lineHeight: 14 * 1.2 }}>
        {children}
      </Text>
    </View>
  );
}
