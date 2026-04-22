import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';
import { PacoDivider } from './PacoDivider';

const meta = {
  title: 'Layout/Separador',
  component: PacoDivider,
  argTypes: {
    inset: { control: { type: 'boolean' } },
  },
  decorators: [
    (Story) => (
      <View className="flex-1 justify-center bg-white p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoDivider>;

export default meta;

type Story = StoryObj<typeof meta>;

export const EntreTextos: Story = {
  render: (args) => (
    <View className="w-full gap-3">
      <Text className="font-lato text-base text-gray-carbon dark:text-gray-niebla">Bloque superior</Text>
      <PacoDivider {...args} />
      <Text className="font-lato text-base text-gray-carbon dark:text-gray-niebla">Bloque inferior</Text>
    </View>
  ),
};

export const ConIndent: Story = {
  args: { inset: true },
  render: (args) => (
    <View className="w-full">
      <Text className="px-4 font-lato text-sm text-gray-pizarra">Seccion alineada</Text>
      <PacoDivider {...args} className="mt-2" />
    </View>
  ),
};
