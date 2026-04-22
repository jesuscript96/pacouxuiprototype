import type { IconProps, IconWeight } from 'phosphor-react-native';
import {
  Bell,
  EnvelopeSimple,
  Heart,
  House,
  Info,
  MagnifyingGlass,
  User,
} from 'phosphor-react-native';

const ICON_MAP = {
  house: House,
  user: User,
  info: Info,
  bell: Bell,
  envelope: EnvelopeSimple,
  heart: Heart,
  search: MagnifyingGlass,
} as const;

export type PacoIconName = keyof typeof ICON_MAP;

export type PacoIconProps = Omit<IconProps, 'weight'> & {
  name: PacoIconName;
  /** Phosphor Bold por defecto (manual de marca). */
  weight?: IconWeight;
};

export function PacoIcon({ name, weight = 'bold', color = '#3148c8', size = 28, ...rest }: PacoIconProps) {
  const Cmp = ICON_MAP[name];
  return <Cmp weight={weight} color={color} size={size} {...rest} />;
}
