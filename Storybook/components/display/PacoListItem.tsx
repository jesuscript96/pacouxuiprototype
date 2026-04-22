import { Pressable, Text, View, type PressableProps } from 'react-native';
import { CaretRight } from 'phosphor-react-native';
import { pacoColors } from '../../constants/theme';
import { PacoIcon, type PacoIconName } from '../ui/PacoIcon';

export type PacoListItemProps = Omit<PressableProps, 'children'> & {
  title: string;
  subtitle?: string;
  icon?: PacoIconName;
  showChevron?: boolean;
};

export function PacoListItem({
  title,
  subtitle,
  icon = 'house',
  showChevron = true,
  className,
  ...rest
}: PacoListItemProps) {
  return (
    <Pressable
      accessibilityRole="button"
      className={`flex-row items-center gap-3 border-b border-gray-humo py-3 dark:border-gray-pizarra ${className ?? ''}`}
      {...rest}
    >
      <View className="rounded-xl bg-paco-blue-soft p-2 dark:bg-gray-pizarra">
        <PacoIcon name={icon} size={22} color={pacoColors.blue} />
      </View>
      <View className="min-w-0 flex-1">
        <Text className="font-lato-bold text-base text-gray-carbon dark:text-gray-niebla" numberOfLines={1}>
          {title}
        </Text>
        {subtitle ? (
          <Text className="mt-0.5 font-lato text-sm text-gray-pizarra dark:text-gray-humo" numberOfLines={2}>
            {subtitle}
          </Text>
        ) : null}
      </View>
      {showChevron ? <CaretRight color={pacoColors.grayPizarra} size={20} weight="bold" /> : null}
    </Pressable>
  );
}
