import type { Meta, StoryObj } from '@storybook/react-native';
import { ScrollView, View } from 'react-native';
import { PacoBanner } from './PacoBanner';

const meta = {
  title: 'Feedback/Banner',
  component: PacoBanner,
  argTypes: {
    variant: { control: { type: 'select' }, options: ['info', 'success', 'warning', 'error'] },
  },
  decorators: [
    (Story) => (
      <ScrollView className="flex-1 bg-gray-niebla p-4 dark:bg-gray-carbon">
        <View className="gap-4">
          <Story />
        </View>
      </ScrollView>
    ),
  ],
} satisfies Meta<typeof PacoBanner>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Info: Story = {
  args: {
    variant: 'info',
    title: 'Nueva encuesta disponible',
    message: 'Tu equipo tiene hasta el viernes para responder.',
  },
};

export const Todos: Story = {
  args: { title: '', variant: 'info' },
  render: () => (
    <>
      <PacoBanner variant="success" title="Cambios guardados" message="La plantilla se actualizo correctamente." />
      <PacoBanner variant="warning" title="Accion requerida" message="Faltan 2 aprobaciones para cerrar el ciclo." />
      <PacoBanner variant="error" title="No se pudo enviar" message="Revisa tu conexion e intenta de nuevo." />
    </>
  ),
};
