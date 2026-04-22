import type { Meta, StoryObj } from '@storybook/react-native';
import { useState } from 'react';
import { Text, View } from 'react-native';
import { action } from 'storybook/actions';
import { PacoChip } from './PacoChip';

const FILTROS = ['Todos', 'Activos', 'Archivados'] as const;

function ChipRow() {
  const [sel, setSel] = useState<string>('Todos');
  return (
    <View className="flex-row flex-wrap gap-2">
      {FILTROS.map((f) => (
        <PacoChip
          key={f}
          selected={sel === f}
          onPress={() => {
            setSel(f);
            action('filtro')(f);
          }}
        >
          {f}
        </PacoChip>
      ))}
    </View>
  );
}

const meta = {
  title: 'Navegacion/Ficha',
  component: PacoChip,
  decorators: [
    (Story) => (
      <View className="flex-1 justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoChip>;

export default meta;

type Story = StoryObj<typeof meta>;

export const UnChip: Story = {
  args: {
    children: 'Recursos humanos',
    selected: false,
    onPress: action('onPress'),
  },
};

export const FilaDeFiltros: Story = {
  args: { children: 'Filtro', selected: false },
  render: () => (
    <View className="gap-3">
      <Text className="font-lato-bold text-sm text-gray-pizarra dark:text-gray-humo">Filtrar por estado</Text>
      <ChipRow />
    </View>
  ),
};
