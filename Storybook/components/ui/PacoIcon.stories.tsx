import type { Meta, StoryObj } from '@storybook/react-native';
import { View } from 'react-native';
import { PacoIcon } from './PacoIcon';
import { pacoColors } from '../../constants/theme';

const meta = {
  title: 'Acciones/Icono',
  component: PacoIcon,
  decorators: [
    (Story) => (
      <View className="flex-1 items-center justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
  argTypes: {
    name: { control: { type: 'select' }, options: ['house', 'user', 'info', 'bell', 'envelope', 'heart', 'search'] },
    size: { control: { type: 'number', min: 16, max: 64, step: 2 } },
    weight: { control: { type: 'select' }, options: ['bold', 'regular', 'fill', 'duotone'] },
    color: { control: { type: 'color' } },
  },
  parameters: {
    notes: 'Wrapper Phosphor con peso Bold por defecto. Amplùa ICON_MAP con mùs iconos del set.',
  },
} satisfies Meta<typeof PacoIcon>;

export default meta;

type Story = StoryObj<typeof meta>;

export const BoldDefault: Story = {
  args: {
    name: 'house',
    size: 36,
    color: pacoColors.blue,
    weight: 'bold',
  },
};

export const User: Story = {
  args: {
    name: 'user',
    size: 40,
    color: pacoColors.accent,
    weight: 'bold',
  },
};
