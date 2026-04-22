import { action } from 'storybook/actions';
import type { Meta, StoryObj } from '@storybook/react-native';
import { View } from 'react-native';
import { PacoButton } from './PacoButton';

const meta = {
  title: 'Acciones/Boton',
  component: PacoButton,
  decorators: [
    (Story) => (
      <View className="flex-1 items-center justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
  argTypes: {
    variant: { control: { type: 'select' }, options: ['primary', 'secondary', 'outline', 'ghost'] },
    loading: { control: { type: 'boolean' } },
    disabled: { control: { type: 'boolean' } },
  },
  parameters: {
    notes: 'CTA con Gordita Bold. Primario en Naranja (acento estratùgico).',
  },
} satisfies Meta<typeof PacoButton>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Primary: Story = {
  args: {
    children: 'Continuar',
    variant: 'primary',
    onPress: action('onPress'),
  },
};

export const Secondary: Story = {
  args: {
    children: 'Secundario',
    variant: 'secondary',
    onPress: action('onPress'),
  },
};

export const Outline: Story = {
  args: {
    children: 'Outline',
    variant: 'outline',
    onPress: action('onPress'),
  },
};

export const Ghost: Story = {
  args: {
    children: 'Ghost',
    variant: 'ghost',
    onPress: action('onPress'),
  },
};

export const Loading: Story = {
  args: {
    children: 'Cargando',
    variant: 'primary',
    loading: true,
  },
};

export const Disabled: Story = {
  args: {
    children: 'Deshabilitado',
    variant: 'primary',
    disabled: true,
  },
};
