import { Text, View, type ViewProps } from 'react-native';
import { Info, WarningCircle, CheckCircle, XCircle } from 'phosphor-react-native';
import { pacoColors } from '../../constants/theme';

export type PacoBannerVariant = 'info' | 'success' | 'warning' | 'error';

export type PacoBannerProps = ViewProps & {
  title: string;
  message?: string;
  variant?: PacoBannerVariant;
};

const cfg: Record<
  PacoBannerVariant,
  { bg: string; border: string; text: string; icon: typeof Info; iconColor: string }
> = {
  info: {
    bg: 'bg-paco-blue-soft',
    border: 'border-paco-blue',
    text: 'text-paco-blue dark:text-gray-niebla',
    icon: Info,
    iconColor: pacoColors.blue,
  },
  success: {
    bg: 'bg-gray-niebla',
    border: 'border-emphasis-green',
    text: 'text-gray-carbon dark:text-gray-niebla',
    icon: CheckCircle,
    iconColor: pacoColors.emphasisGreen,
  },
  warning: {
    bg: 'bg-gray-niebla',
    border: 'border-emphasis-yellow',
    text: 'text-gray-carbon dark:text-gray-niebla',
    icon: WarningCircle,
    iconColor: '#b45309',
  },
  error: {
    bg: 'bg-gray-niebla',
    border: 'border-emphasis-red',
    text: 'text-gray-carbon dark:text-gray-niebla',
    icon: XCircle,
    iconColor: pacoColors.emphasisRed,
  },
};

export function PacoBanner({ title, message, variant = 'info', className, ...rest }: PacoBannerProps) {
  const c = cfg[variant];
  const Icon = c.icon;
  return (
    <View className={`flex-row gap-3 rounded-xl border-l-4 p-4 ${c.bg} ${c.border} ${className ?? ''}`} {...rest}>
      <Icon size={24} color={c.iconColor} weight="bold" />
      <View className="min-w-0 flex-1">
        <Text className={`font-gordita text-base ${c.text}`} style={{ lineHeight: 18 * 1.2 }}>
          {title}
        </Text>
        {message ? (
          <Text className={`mt-1 font-lato text-sm ${c.text}`} style={{ lineHeight: 16 * 1.5 }}>
            {message}
          </Text>
        ) : null}
      </View>
    </View>
  );
}
