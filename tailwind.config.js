/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      colors: {
        // Professional "Baidu-inspired" clean tech palette
        primary: {
          50: '#eef6ff',
          100: '#d9eaff',
          500: '#2932e1', // Primary Brand Blue
          600: '#1b23c2',
          700: '#151b98',
        },
        action: {
          success: '#10b981', // Dispense, Save
          danger: '#ef4444',  // Quarantine, Delete
          warning: '#f59e0b', // Low Stock, Expiring
          info: '#3b82f6',    // Tooltips, Data
        },
        dark: {
          bg: '#0f172a',
          surface: '#1e293b',
          border: '#334155'
        }
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '1.5rem', // Smooth curves
        '3xl': '2rem',
      },
      transitionProperty: {
        'height': 'height',
        'spacing': 'margin, padding',
      }
    },
  },
  plugins: [],
}