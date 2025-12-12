/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          light: '#FDF6E3',   // Um creme suave (Baseado nos Neutros Quentes)
          primary: '#C05621', // Terracota vibrante (Baseado no Outono Escuro)
          secondary: '#5F6F3A', // Verde Musgo elegante (Baseado no Outono Universal)
          dark: '#2D3748',    // Texto escuro (para contraste)
        }
      },
      fontFamily: {
        sans: ['Figtree', 'sans-serif'], // Fonte padrão limpa
      },
    },
  },
  plugins: [],
}