import type { Preview } from '@storybook/react-native';
import { withBackgrounds } from '@storybook/addon-ondevice-backgrounds';
import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Appearance, Text, View } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { usePacoFonts } from '../hooks/usePacoFonts';
import { pacoColors } from '../constants/theme';

function FontGate({ children }: { children: React.ReactNode }) {
  const [loaded, err] = usePacoFonts();
  if (err) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-niebla p-4">
        <Text className="font-lato text-center text-paco-blue">Error cargando fuentes: {err.message}</Text>
      </View>
    );
  }
  if (!loaded) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-niebla">
        <ActivityIndicator size="large" color={pacoColors.blue} />
      </View>
    );
  }
  return <>{children}</>;
}

/** Alterna esquema claro/oscuro del sistema para probar utilidades `dark:`. */
function ThemeClassSync({ children }: { children: React.ReactNode }) {
  const [scheme, setScheme] = useState(Appearance.getColorScheme());

  useEffect(() => {
    const sub = Appearance.addChangeListener(({ colorScheme }) => {
      setScheme(colorScheme);
    });
    return () => sub.remove();
  }, []);

  return (
    <View className={`flex-1 ${scheme === 'dark' ? 'dark' : ''}`} key={scheme ?? 'light'}>
      {children}
    </View>
  );
}

const preview: Preview = {
  decorators: [
    (Story) => (
      <SafeAreaProvider>
        <FontGate>
          <ThemeClassSync>
            <Story />
          </ThemeClassSync>
        </FontGate>
      </SafeAreaProvider>
    ),
    withBackgrounds,
  ],
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
    backgrounds: {
      default: 'niebla',
      values: [
        { name: 'niebla', value: pacoColors.grayNiebla },
        { name: 'humo', value: pacoColors.grayHumo },
        { name: 'carbon', value: pacoColors.grayCarbon },
        { name: 'azul_paco', value: pacoColors.blue },
        { name: 'azul_suave', value: pacoColors.blueSoft },
        { name: 'blanco', value: '#FFFFFF' },
      ],
    },
  },
};

export default preview;
