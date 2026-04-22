import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';

const meta = {
  title: 'Foundations/Énfasis',
  component: View,
} satisfies Meta<typeof View>;

export default meta;

type Story = StoryObj<typeof meta>;

const chips = [
  { label: 'Error / alerta', className: 'bg-emphasis-red' },
  { label: 'Advertencia', className: 'bg-emphasis-yellow' },
  { label: 'Éxito', className: 'bg-emphasis-green' },
  { label: 'Info violeta', className: 'bg-emphasis-violet' },
  { label: 'Info mora', className: 'bg-emphasis-mora' },
] as const;

export const Chips: Story = {
  render: () => (
    <View className="flex-1 flex-row flex-wrap gap-3 bg-gray-niebla p-4 dark:bg-gray-carbon">
      {chips.map((c) => (
        <View key={c.label} className={`rounded-full px-4 py-2 ${c.className}`}>
          <Text className="font-lato-bold text-sm text-white">{c.label}</Text>
        </View>
      ))}
    </View>
  ),
};
