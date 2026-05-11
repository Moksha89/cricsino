import defaultTheme from 'tailwindcss/defaultTheme';

const svgToDataUri = require("mini-svg-data-uri");
const colors = require("tailwindcss/colors");
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,tsx,vue}',
    ],
    prefix: "",
    theme: {
        extend: {
            colors: () => {
                return {
                    emerald: colors.amber,
                    // Premium dark purple casino theme
                    gray: {
                        50: "#E8E6F0",
                        100: "#D0CCE0",
                        150: "#B8B3D0",
                        200: "#A099C0",
                        250: "#8880B0",
                        300: "#6B6B85",
                        350: "#5A5A70",
                        400: "#A1A1B5",
                        450: "#8c8c9f",
                        500: "#6B6B85",
                        550: "#4A4A60",
                        600: "#2A2A45",
                        650: "#1C1B3A",
                        700: "#1C1B3A",
                        750: "#161530",
                        800: "#14142B",
                        850: "#101022",
                        900: "#0B0B1F",
                        950: "#070716"
                    },
                    zinc: {
                        50: "#E8E6F0",
                        100: "#D0CCE0",
                        150: "#B8B3D0",
                        200: "#A099C0",
                        250: "#8880B0",
                        300: "#6B6B85",
                        350: "#5A5A70",
                        400: "#A1A1B5",
                        450: "#8c8c9f",
                        500: "#6B6B85",
                        550: "#4A4A60",
                        600: "#2A2A45",
                        650: "#1C1B3A",
                        700: "#1C1B3A",
                        750: "#161530",
                        800: "#14142B",
                        850: "#101022",
                        900: "#0B0B1F",
                        950: "#070716"
                    },
                    primary: "#7C3AED",
                    'primary-light': "#A855F7",
                    'primary-dark': "#5B21B6",
                    secondary: "#C084FC",
                    info: "#38BDF8",
                    success: "#10B981",
                    warning: "#F59E0B",
                    error: "#EF4444",
                    danger: "#EF4444",
                    accent: "#C084FC",
                    gold: "#FACC15",
                    surface: "#14142B",
                    'surface-light': "#1C1B3A",
                    'surface-dark': "#101022",
                };
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', ...defaultTheme.fontFamily.sans]
            },
            borderColor: {
                DEFAULT: 'rgba(255,255,255,0.08)',
            },
            backgroundImage: (theme) => ({
                'multiselect-caret': `url("${svgToDataUri(
                    `<svg viewBox="0 0 320 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"></path></svg>`,
                )}")`,
                'multiselect-spinner': `url("${svgToDataUri(
                    `<svg viewBox="0 0 512 512" fill="${theme('colors.emerald.500')}" xmlns="http://www.w3.org/2000/svg"><path d="M456.433 371.72l-27.79-16.045c-7.192-4.152-10.052-13.136-6.487-20.636 25.82-54.328 23.566-118.602-6.768-171.03-30.265-52.529-84.802-86.621-144.76-91.424C262.35 71.922 256 64.953 256 56.649V24.56c0-9.31 7.916-16.609 17.204-15.96 81.795 5.717 156.412 51.902 197.611 123.408 41.301 71.385 43.99 159.096 8.042 232.792-4.082 8.369-14.361 11.575-22.424 6.92z"></path></svg>`,
                )}")`,
                'multiselect-remove': `url("${svgToDataUri(
                    `<svg viewBox="0 0 320 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M207.6 256l107.72-107.72c6.23-6.23 6.23-16.34 0-22.58l-25.03-25.03c-6.23-6.23-16.34-6.23-22.58 0L160 208.4 52.28 100.68c-6.23-6.23-16.34-6.23-22.58 0L4.68 125.7c-6.23 6.23-6.23 16.34 0 22.58L112.4 256 4.68 363.72c-6.23 6.23-6.23 16.34 0 22.58l25.03 25.03c6.23 6.23 16.34 6.23 22.58 0L160 303.6l107.72 107.72c6.23 6.23 16.34 6.23 22.58 0l25.03-25.03c6.23-6.23 6.23-16.34 0-22.58L207.6 256z"></path></svg>`,
                )}")`,
            }),
            transitionProperty: {
                'spacing': 'margin, padding',
            },
            keyframes: {
                "accordion-down": {
                    from: { height: 0 },
                    to: { height: "var(--radix-accordion-content-height)" },
                },
                "accordion-up": {
                    from: { height: "var(--radix-accordion-content-height)" },
                    to: { height: 0 },
                },
            },
            animation: {
                "accordion-down": "accordion-down 0.2s ease-out",
                "accordion-up": "accordion-up 0.2s ease-out",
            },
        }
    },

    plugins: [
        require("@tailwindcss/typography"),
        require("tailwindcss-gradient"),
        require("tailwind-scrollbar-hide"),
        require("tailwind-scrollbar"),
        require("tailwind-bootstrap-grid")({
            containerMaxWidths: {
                sm: "540px",
                md: "720px",
                lg: "960px",
                xl: "1140px",
            },
        }),
    ],
    corePlugins: {
        container: false,
        textOpacity: false,
        backgroundOpacity: true,
        borderOpacity: false,
        divideOpacity: false,
        placeholderOpacity: false,
        ringOpacity: true,
    },
    darkMode: ["class"],
    mode: "jit",
};
