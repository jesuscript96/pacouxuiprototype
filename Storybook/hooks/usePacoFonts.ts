import {
  Lato_400Regular,
  Lato_400Regular_Italic,
  Lato_700Bold,
} from '@expo-google-fonts/lato';
import { useFonts } from 'expo-font';

/**
 * Lato (Google Fonts). Gordita: copia los .ttf del kit en materiales/Tipografia/Gordita
 * (ver LEEME.txt) y registrarlos aqui; mientras tanto se usa Lato Bold como sustituto.
 */
export function usePacoFonts(): [boolean, Error | null] {
  return useFonts({
    'Gordita-Bold': Lato_700Bold,
    'Lato-Regular': Lato_400Regular,
    'Lato-Bold': Lato_700Bold,
    'Lato-Italic': Lato_400Regular_Italic,
  });
}
