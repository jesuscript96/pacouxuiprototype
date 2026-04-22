import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';
import { CurvedBackground } from './CurvedBackground';
import { pacoColors } from '../../constants/theme';

const meta = {
  title: 'Layout/Fondo curvas',
  component: CurvedBackground,
  argTypes: {
    waves: { control: { type: 'range', min: 1, max: 10, step: 1 } },
    strokeWidth: { control: { type: 'range', min: 1, max: 6, step: 0.5 } },
    lineColor: { control: { type: 'color' } },
    backgroundColor: { control: { type: 'color' } },
  },
  parameters: {
    notes: 'Fondo con lùneas curvas; ajusta ondas y colores para alinearlo a ilustraciones del manual.',
  },
} satisfies Meta<typeof CurvedBackground>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    waves: 4,
    strokeWidth: 2,
    lineColor: pacoColors.blue,
    backgroundColor: pacoColors.grayNiebla,
  },
  render: (args) => (
    <CurvedBackground {...args} className="min-h-[360px] w-full rounded-2xl">
      <View className="flex-1 justify-end p-6">
        <Text className="font-gordita text-lg text-paco-blue dark:text-gray-niebla" style={{ lineHeight: 22 * 1.2 }}>
          Contenido sobre curvas
        </Text>
        <Text className="mt-1 font-lato text-sm text-gray-pizarra dark:text-gray-humo" style={{ lineHeight: 14 * 1.5 }}>
          Usa este layout como base de pantallas de bienvenida o onboarding.
        </Text>
      </View>
    </CurvedBackground>
  ),
};
