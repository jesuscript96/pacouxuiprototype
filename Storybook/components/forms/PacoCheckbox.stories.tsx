import type { Meta, StoryObj } from '@storybook/react-native';
import { useState } from 'react';
import { View } from 'react-native';
import { action } from 'storybook/actions';
import { PacoCheckbox } from './PacoCheckbox';

function StatefulCheckbox(props: { label: string; initial?: boolean }) {
  const [checked, setChecked] = useState(props.initial ?? false);
  return (
    <PacoCheckbox
      label={props.label}
      checked={checked}
      onPress={() => {
        setChecked(!checked);
        action('onPress')(!checked);
      }}
    />
  );
}

const meta = {
  title: 'Formularios/Casilla',
  component: PacoCheckbox,
  decorators: [
    (Story) => (
      <View className="flex-1 justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoCheckbox>;

export default meta;

type Story = StoryObj<typeof meta>;

export const AceptarTerminos: Story = {
  args: { label: '', checked: false },
  render: () => <StatefulCheckbox label="Acepto los terminos y la politica de privacidad." initial={false} />,
};

export const Marcado: Story = {
  args: { label: '', checked: true },
  render: () => <StatefulCheckbox label="Recordarme en este dispositivo" initial />,
};
