import type { Meta, StoryObj } from '@storybook/react-native';
import { Text } from 'react-native';
import { BrandRadialBackground } from '../brand/BrandRadialBackground';

const meta = {
  title: 'Foundations/Degradado',
  component: BrandRadialBackground,
  argTypes: {
    intensity: { control: { type: 'range', min: 0.3, max: 1, step: 0.05 } },
  },
} satisfies Meta<typeof BrandRadialBackground>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Institucional: Story = {
  args: {
    intensity: 0.85,
  },
  render: (args) => (
    <BrandRadialBackground {...args} className="min-h-[320px] w-full rounded-2xl p-6">
      <Text className="font-gordita text-xl text-white" style={{ lineHeight: 24 * 1.2 }}>
        Degradado marca (simulacion radial)
      </Text>
      <Text className="mt-2 max-w-sm font-lato text-base text-white/90" style={{ lineHeight: 16 * 1.5 }}>
        Azul suave hacia azul marca (tokens en constants/theme). Ajusta stops para igualar al PDF.
      </Text>
    </BrandRadialBackground>
  ),
};
