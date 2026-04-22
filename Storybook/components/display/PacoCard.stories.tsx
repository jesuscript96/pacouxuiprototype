import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';
import { PacoButton } from '../ui/PacoButton';
import { PacoCard } from './PacoCard';

const meta = {
  title: 'Datos/Tarjeta',
  component: PacoCard,
  decorators: [
    (Story) => (
      <View className="flex-1 justify-center bg-gray-niebla p-4 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoCard>;

export default meta;

type Story = StoryObj<typeof meta>;

export const SoloTitulo: Story = {
  args: {
    title: 'Resumen de clima laboral',
    subtitle: 'Ultimos 30 dias · 142 respuestas',
  },
};

export const ConAcciones: Story = {
  args: { title: 'Placeholder', subtitle: '' },
  render: () => (
    <PacoCard title="Objetivos Q2" subtitle="3 metas activas">
      <Text className="font-lato text-sm leading-body text-gray-pizarra dark:text-gray-humo">
        Revisa el avance con tu equipo y marca los hitos completados antes del viernes.
      </Text>
      <View className="mt-4 flex-row gap-2">
        <PacoButton variant="primary" onPress={() => {}}>
          Ver detalle
        </PacoButton>
        <PacoButton variant="outline" onPress={() => {}}>
          Compartir
        </PacoButton>
      </View>
    </PacoCard>
  ),
};
