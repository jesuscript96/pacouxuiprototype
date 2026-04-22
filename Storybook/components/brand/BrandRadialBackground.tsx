import { LinearGradient } from 'expo-linear-gradient';
import { StyleSheet, View, type ViewProps } from 'react-native';
import { pacoGradient } from '../../constants/theme';

export type BrandRadialBackgroundProps = ViewProps & {
  /** Intensidad del centro (0 a 1): mas alto = mas contraste hacia los bordes */
  intensity?: number;
};

/**
 * Degradado institucional (azul suave del kit hacia azul marca).
 */
export function BrandRadialBackground({
  style,
  intensity = 0.85,
  children,
  ...rest
}: BrandRadialBackgroundProps) {
  return (
    <View style={[styles.wrap, style]} {...rest}>
      <LinearGradient
        colors={[pacoGradient.start, pacoGradient.mid, pacoGradient.end]}
        locations={[0, Math.min(0.5 * intensity, 0.85), 1]}
        start={{ x: 0.15, y: 0.1 }}
        end={{ x: 0.95, y: 0.95 }}
        style={StyleSheet.absoluteFill}
      />
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    overflow: 'hidden',
    position: 'relative',
  },
});
