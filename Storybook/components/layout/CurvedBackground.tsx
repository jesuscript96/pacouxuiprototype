import { View, type ViewProps } from 'react-native';
import Svg, { Path } from 'react-native-svg';
import { pacoColors } from '../../constants/theme';

export type CurvedBackgroundProps = ViewProps & {
  lineColor?: string;
  backgroundColor?: string;
  /** Numero de ondas aproximadas */
  waves?: number;
  strokeWidth?: number;
};

/**
 * Fondo con lineas curvas (marca). Ajusta waves y colores desde Storybook.
 */
export function CurvedBackground({
  lineColor = pacoColors.blue,
  backgroundColor = pacoColors.grayNiebla,
  waves = 4,
  strokeWidth = 2,
  style,
  children,
  ...rest
}: CurvedBackgroundProps) {
  const w = 400;
  const h = 800;
  const step = h / Math.max(waves, 1);
  const paths: string[] = [];
  for (let i = 0; i < waves; i++) {
    const y = step * (i + 0.5);
    const amp = 40 + (i % 3) * 12;
    const phase = i * 20;
    paths.push(
      `M -20 ${y} Q ${w * 0.25 + phase} ${y - amp} ${w * 0.5} ${y} T ${w + 20} ${y + amp * 0.3}`
    );
  }
  const d = paths.join(' ');

  return (
    <View className="flex-1 overflow-hidden" style={[{ backgroundColor }, style]} {...rest}>
      <Svg
        width="100%"
        height="100%"
        viewBox={`0 0 ${w} ${h}`}
        preserveAspectRatio="xMidYMid slice"
        style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}
      >
        <Path d={d} stroke={lineColor} strokeWidth={strokeWidth} fill="none" opacity={0.45} />
      </Svg>
      {children}
    </View>
  );
}
