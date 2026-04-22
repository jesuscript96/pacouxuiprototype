import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';
import { PacoBadge, type PacoBadgeTone } from './PacoBadge';

const meta = {
  title: 'Datos/Etiqueta',
  component: PacoBadge,
  argTypes: {
    tone: { control: { type: 'select' }, options: ['accent', 'blue', 'neutral', 'success', 'warning', 'danger'] },
    children: { control: { type: 'text' } },
  },
  decorators: [
    (Story) => (
      <View className="flex-1 justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoBadge>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Acento: Story = {
  args: { children: 'Nuevo', tone: 'accent' },
};

export const TodosLosTonos: Story = {
  args: { children: 'Ejemplo', tone: 'accent' },
  render: () => {
    const tones: PacoBadgeTone[] = ['accent', 'blue', 'neutral', 'success', 'warning', 'danger'];
    const labels: Record<PacoBadgeTone, string> = {
      accent: 'Destacado',
      blue: 'Equipo',
      neutral: 'Borrador',
      success: 'Listo',
      warning: 'Pendiente',
      danger: 'Urgente',
    };
    return (
      <View className="flex-row flex-wrap gap-2">
        {tones.map((t) => (
          <PacoBadge key={t} tone={t}>
            {labels[t]}
          </PacoBadge>
        ))}
      </View>
    );
  },
};

export const ConTexto: Story = {
  args: { children: 'RRHH', tone: 'blue' },
  render: () => (
    <View className="gap-2">
      <Text className="font-lato text-sm text-gray-pizarra dark:text-gray-humo">Ejemplo en contexto:</Text>
      <View className="flex-row items-center gap-2">
        <PacoBadge tone="blue">RRHH</PacoBadge>
        <Text className="font-lato text-base text-gray-carbon dark:text-gray-niebla">Encuesta trimestral</Text>
      </View>
    </View>
  ),
};
