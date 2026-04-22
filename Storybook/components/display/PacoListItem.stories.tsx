import type { Meta, StoryObj } from '@storybook/react-native';
import { ScrollView, View } from 'react-native';
import { action } from 'storybook/actions';
import { PacoListItem } from './PacoListItem';

const meta = {
  title: 'Datos/Fila de lista',
  component: PacoListItem,
  decorators: [
    (Story) => (
      <View className="flex-1 bg-gray-niebla p-4 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoListItem>;

export default meta;

type Story = StoryObj<typeof meta>;

export const UnaFila: Story = {
  args: {
    title: 'Mis encuestas',
    subtitle: '3 pendientes de responder',
    icon: 'envelope',
    onPress: action('onPress'),
  },
};

export const ListaCorta: Story = {
  args: { title: 'Ejemplo', subtitle: '', icon: 'house' },
  render: () => (
    <ScrollView className="rounded-xl bg-white p-2 dark:bg-gray-carbon">
      <PacoListItem title="Inicio" subtitle="Resumen y alertas" icon="house" onPress={action('inicio')} />
      <PacoListItem title="Mensajes" subtitle="Chat con tu equipo" icon="bell" onPress={action('mensajes')} />
      <PacoListItem title="Buscar" subtitle="Personas y equipos" icon="search" showChevron={false} onPress={action('buscar')} />
    </ScrollView>
  ),
};
