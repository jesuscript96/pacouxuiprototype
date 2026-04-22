import type { Meta, StoryObj } from '@storybook/react-native';
import { Text, View } from 'react-native';
import { PacoChatIllustration } from './PacoChatIllustration';

const meta = {
  title: 'Brand/Ilustraciones/Chat',
  component: PacoChatIllustration,
  parameters: {
    notes: 'Fuente: materiales/Iconos app/Chat.svg (colores de marca).',
  },
  argTypes: {
    size: { control: { type: 'number', min: 48, max: 200, step: 4 } },
  },
  decorators: [
    (Story) => (
      <View className="flex-1 items-center justify-center bg-gray-niebla p-6 dark:bg-gray-carbon">
        <Story />
      </View>
    ),
  ],
} satisfies Meta<typeof PacoChatIllustration>;

export default meta;

type Story = StoryObj<typeof meta>;

export const PorDefecto: Story = {
  args: { size: 120 },
};

export const EnTarjeta: Story = {
  render: () => (
    <View className="items-center rounded-2xl bg-white p-6 dark:bg-gray-carbon">
      <PacoChatIllustration size={100} />
      <Text className="mt-4 text-center font-gordita text-lg text-paco-blue dark:text-paco-blue-soft">
        Conversaciones
      </Text>
      <Text className="mt-1 text-center font-lato text-sm text-gray-pizarra dark:text-gray-humo">
        Ilustracion del kit de marca
      </Text>
    </View>
  ),
};
