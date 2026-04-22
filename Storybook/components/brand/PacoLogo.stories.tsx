import type { Meta, StoryObj } from '@storybook/react-native';
import { View } from 'react-native';
import { PacoLogo } from './PacoLogo';

const meta = {
  title: 'Brand/Logo',
  component: PacoLogo,
  decorators: [
    (Story) => (
      <View className="flex-1 items-start justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
  argTypes: {
    variant: { control: { type: 'select' }, options: ['paco', 'paco-app'] },
    size: { control: { type: 'select' }, options: ['sm', 'md', 'lg'] },
  },
  parameters: {
    notes:
      'Logos editables en materiales/Logo (Paco y Paco App). Sustituye este placeholder por export SVG/PNG.',
  },
} satisfies Meta<typeof PacoLogo>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Paco: Story = {
  args: { variant: 'paco', size: 'md' },
};

export const PacoApp: Story = {
  args: { variant: 'paco-app', size: 'lg' },
};
