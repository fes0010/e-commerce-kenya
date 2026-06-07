/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            colors: {
                navyBlue: "var(--theme-navyBlue)",
                lightOrange: "var(--theme-lightOrange)",
                darkGreen: "var(--theme-darkGreen)",
                darkBlue: "var(--theme-darkBlue)",
                darkPink: "var(--theme-darkPink)",
                nav: {
                    border: "var(--theme-nav-border, var(--theme-navyBlue))",
                    text: "var(--theme-nav-text, var(--theme-navyBlue))",
                }
            },

            fontFamily: {
                poppins: ["var(--theme-font-poppins)", "sans-serif"],
                dmserif: ["var(--theme-font-dmserif)", "serif"],
            },
        }
    },

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};
