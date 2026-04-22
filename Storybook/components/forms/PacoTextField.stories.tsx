import type { Meta, StoryObj } from '@storybook/react-native';
import { ScrollView, View } from 'react-native';
import { PacoTextField } from './PacoTextField';

const meta = {
  title: 'Formularios/Campo de texto',
  component: PacoTextField,
  decorators: [
    (Story) => (
      <ScrollView className="flex-1 bg-gray-niebla p-4 dark:bg-gray-carbon">
        <View className="max-w-md">
          <Story />
        </View>
      </ScrollView>
    ),
  ],
  argTypes: {
    label: { control: { type: 'text' } },
    placeholder: { control: { type: 'text' } },
    hint: { control: { type: 'text' } },
    errorMessage: { control: { type: 'text' } },
    editable: { control: { type: 'boolean' } },
  },
} satisfies Meta<typeof PacoTextField>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Basico: Story = {
  args: {
    label: 'Correo',
    placeholder: 'hola@paco.app',
    hint: 'Usaremos este correo para avisos importantes.',
  },
};

export const ConError: Story = {
  args: {
    label: 'Contrasena',
    placeholder: '********',
    errorMessage: 'Minimo 8 caracteres.',
    secureTextEntry: true,
  },
};

export const SoloLectura: Story = {
  args: {
    label: 'ID empleado',
    value: 'EMP-10294',
    editable: false,
  },
};
