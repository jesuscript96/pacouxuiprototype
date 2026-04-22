import type { Meta, StoryObj } from '@storybook/react-native';
import { View } from 'react-native';
import { PacoAvatar } from './PacoAvatar';

const meta = {
  title: 'Brand/Avatar',
  component: PacoAvatar,
  decorators: [
    (Story) => (
      <View className="flex-1 items-center justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
  argTypes: {
    variant: { control: { type: 'select' }, options: ['paco', 'paco-app'] },
    size: { control: { type: 'number', min: 32, max: 96, step: 4 } },
    label: { control: { type: 'text' } },
  },
} satisfies Meta<typeof PacoAvatar>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: { variant: 'paco', size: 56 },
};

export const PacoApp: Story = {
  args: { variant: 'paco-app', size: 56, label: 'A' },
};
