import type { Meta, StoryObj } from '@storybook/react-native';
import { ScrollView, Text, View } from 'react-native';

const meta = {
  title: 'Foundations/Tipografia',
  component: View,
} satisfies Meta<typeof View>;

export default meta;

type Story = StoryObj<typeof meta>;

function TypeSamples() {
  return (
    <ScrollView className="flex-1 bg-gray-niebla p-4 dark:bg-gray-carbon">
      <Text className="mb-2 font-gordita text-2xl text-paco-blue dark:text-gray-niebla" style={{ lineHeight: 28 * 1.2 }}>
        Gordita Bold — Titulo / CTA (interlineado 120%)
      </Text>
      <Text className="mb-6 font-lato text-sm text-gray-pizarra dark:text-gray-humo">
        Gordita: copia los .ttf del kit (materiales/Tipografia/Gordita, LEEME.txt) y registrarlos en usePacoFonts; ahora el hook usa Lato Bold como sustituto.
      </Text>
      <Text className="mb-3 font-lato text-base text-gray-carbon dark:text-gray-niebla" style={{ lineHeight: 16 * 1.5 }}>
        Lato Regular — cuerpo con interlineado 150%. Ideal para parrafos largos en la app Paco. Manten buen contraste sobre Niebla o blanco en modo claro.
      </Text>
      <Text className="mb-3 font-lato-bold text-base text-paco-blue dark:text-gray-niebla" style={{ lineHeight: 16 * 1.5 }}>
        Lato Bold — enfasis dentro del cuerpo. No sustituye a Gordita en titulos principales.
      </Text>
      <Text className="font-lato-italic text-base text-gray-pizarra dark:text-gray-humo" style={{ lineHeight: 16 * 1.5 }}>
        Lato Italic — citas, notas o microcopy secundario con la misma lectura que Regular.
      </Text>
    </ScrollView>
  );
}

export const Muestras: Story = {
  render: () => <TypeSamples />,
};
