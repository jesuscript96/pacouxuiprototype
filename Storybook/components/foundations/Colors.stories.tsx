import type { Meta, StoryObj } from '@storybook/react-native';
import { ScrollView, Text, View } from 'react-native';
import { pacoColors } from '../../constants/theme';

const meta = {
  title: 'Foundations/Colores',
  component: View,
} satisfies Meta<typeof View>;

export default meta;

type Story = StoryObj<typeof meta>;

const SWATCHES: { label: string; token: string; hex: string }[] = [
  { label: 'Azul marca (trazo)', token: 'paco-blue', hex: pacoColors.blue },
  { label: 'Azul suave (relleno)', token: 'paco-blue-soft', hex: pacoColors.blueSoft },
  { label: 'Azul profundo (degradado)', token: 'paco-blue-deep', hex: '#2436a3' },
  { label: 'Coral / acento', token: 'paco-accent', hex: pacoColors.accent },
  { label: 'Gris Niebla', token: 'gray-niebla', hex: pacoColors.grayNiebla },
  { label: 'Gris Humo', token: 'gray-humo', hex: pacoColors.grayHumo },
  { label: 'Gris Pizarra', token: 'gray-pizarra', hex: pacoColors.grayPizarra },
  { label: 'Carbon', token: 'gray-carbon', hex: pacoColors.grayCarbon },
  { label: 'Enfasis Rojo', token: 'emphasis-red', hex: pacoColors.emphasisRed },
  { label: 'Enfasis Amarillo', token: 'emphasis-yellow', hex: pacoColors.emphasisYellow },
  { label: 'Enfasis Verde', token: 'emphasis-green', hex: pacoColors.emphasisGreen },
  { label: 'Enfasis Violeta', token: 'emphasis-violet', hex: pacoColors.emphasisViolet },
  { label: 'Enfasis Mora', token: 'emphasis-mora', hex: pacoColors.emphasisMora },
];

function Palette() {
  return (
    <ScrollView className="flex-1 bg-gray-niebla p-4 dark:bg-gray-carbon">
      <Text className="mb-4 font-gordita text-xl text-paco-blue dark:text-gray-niebla">
        Paleta (kit + materiales/Iconos app/Chat.svg)
      </Text>
      <Text className="mb-6 font-lato text-base leading-body text-gray-pizarra dark:text-gray-humo">
        Azul #3148c8, acento #fb4f33 y suave #cad6fb extraidos del SVG de marca. Refina con el PDF si hace falta.
      </Text>
      <View className="flex-row flex-wrap gap-3">
        {SWATCHES.map((s) => (
          <View key={s.token} className="mb-2 w-[46%]">
            <View
              className="mb-2 h-16 w-full rounded-xl border border-gray-humo"
              style={{ backgroundColor: s.hex }}
            />
            <Text className="font-lato-bold text-sm text-gray-carbon dark:text-gray-niebla">{s.label}</Text>
            <Text className="font-lato text-xs text-gray-pizarra dark:text-gray-humo">{s.hex}</Text>
            <Text className="font-lato-italic text-xs text-gray-pizarra dark:text-gray-humo">{s.token}</Text>
          </View>
        ))}
      </View>
    </ScrollView>
  );
}

export const PaletaCompleta: Story = {
  render: () => <Palette />,
};
